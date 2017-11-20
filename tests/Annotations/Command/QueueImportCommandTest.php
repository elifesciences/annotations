<?php

namespace tests\eLife\Annotations\Command;

use eLife\Annotations\Command\QueueImportCommand;
use eLife\ApiClient\HttpClient;
use eLife\ApiClient\Result\HttpResult;
use eLife\ApiSdk\ApiSdk;
use eLife\Bus\Limit\Limit;
use eLife\Bus\Queue\Mock\WatchableQueueMock;
use eLife\Logging\Monitoring;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Response;
use PHPUnit_Framework_TestCase;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Debug\BufferingLogger;

/**
 * @covers \eLife\Annotations\Command\QueueImportCommand
 */
class QueueImportCommandTest extends PHPUnit_Framework_TestCase
{
    /** @var ApiSdk */
    private $apiSdk;
    /** @var Application */
    private $application;
    /** @var QueueImportCommand */
    private $command;
    /** @var CommandTester */
    private $commandTester;
    private $httpClient;
    private $limit;
    /** @var BufferingLogger */
    private $logger;
    /** @var Monitoring */
    private $monitoring;
    /** @var WatchableQueueMock */
    private $queue;

    /**
     * @before
     */
    public function prepareDependencies()
    {
        $this->httpClient = $this->getMockBuilder(HttpClient::class)
            ->setMethods(['send'])
            ->getMock();
        $this->apiSdk = new ApiSdk($this->httpClient);
        $this->application = new Application();
        $this->limit = $this->createMock(Limit::class);
        $this->logger = new BufferingLogger();
        $this->monitoring = new Monitoring();
        $this->queue = new WatchableQueueMock();
        $this->command = new QueueImportCommand($this->apiSdk, $this->queue, $this->logger, $this->monitoring, $this->limit);
        $this->application->add($this->command);
        $this->commandTester = new CommandTester($this->application->get($this->command->getName()));
    }

    /**
     * @test
     */
    public function it_will_import_items_to_the_queue()
    {
        $this->assertEmpty($this->queue->count());
        $this->httpClient
            ->method('send')
            ->willReturn($this->prepareMockResponse(5));
        $this->commandTesterExecute('all');
        $this->assertEquals(5, $this->queue->count());
        $this->assertStringEndsWith('[OK] All entities queued.', trim($this->commandTester->getDisplay()));
    }

    /**
     * @test
     */
    public function it_will_display_a_progress_bar()
    {
        $this->httpClient
            ->method('send')
            ->willReturn($this->prepareMockResponse(1));
        $this->commandTesterExecute('all');
        $display = trim($this->commandTester->getDisplay());
        $this->assertStringStartsWith('1/1 [============================] 100%', $display);
        $this->assertStringEndsWith('[OK] All entities queued.', $display);
    }

    /**
     * @test
     */
    public function it_will_update_the_log()
    {
        $this->httpClient
            ->method('send')
            ->willReturn($this->prepareMockResponse(1));
        $this->commandTesterExecute('all');
        $expected_logs = [
            [LogLevel::INFO, 'Importing Profiles.', []],
            [LogLevel::INFO, 'Importing 1 item(s) of type "profile".', []],
            [LogLevel::INFO, 'Item (profile, id0) being enqueued.', []],
            [LogLevel::INFO, 'Item (profile, id0) enqueued successfully.', []],
            [LogLevel::INFO, 'All entities queued.', []],
        ];
        $this->assertEquals($expected_logs, $this->logger->cleanLogs());
    }

    /**
     * @test
     */
    public function if_entity_is_passed_it_must_be_valid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Entity with name "invalid" not supported.');
        $this->commandTesterExecute('invalid');
    }

    private function commandTesterExecute($entity = null)
    {
        $execArgs = array_filter(
            [
                'command' => $this->command->getName(),
                'entity' => $entity,
            ]
        );
        $this->commandTester->execute($execArgs);
    }

    private function prepareMockResponse($count = 0) : FulfilledPromise
    {
        $items = [];
        if ($count > 0) {
            for ($i = 0; $i < $count; ++$i) {
                $items[] = [
                    'id' => 'id'.$i,
                    'name' => [
                        'preferred' => 'Preferred name'.$i,
                        'index' => 'Index name'.$i,
                    ],
                ];
            }
        }
        $json = [
            'total' => $count,
            'items' => $items,
        ];

        return new FulfilledPromise(HttpResult::fromResponse(new Response(200, ['Content-Type' => 'application/vnd.elife.profile-list+json; version=1'], json_encode($json))));
    }
}
