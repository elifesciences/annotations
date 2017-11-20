<?php

namespace tests\eLife\Annotations\Command;

use eLife\Annotations\Command\QueueWatchCommand;
use eLife\Bus\Limit\CallbackLimit;
use eLife\Bus\Limit\Limit;
use eLife\Bus\Queue\InternalSqsMessage;
use eLife\Bus\Queue\Mock\WatchableQueueMock;
use eLife\Bus\Queue\QueueItemTransformer;
use eLife\HypothesisClient\ApiSdk as HypothesisSdk;
use eLife\HypothesisClient\HttpClient\HttpClient;
use eLife\Logging\Monitoring;
use PHPUnit_Framework_TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @covers \eLife\Annotations\Command\QueueWatchCommand
 */
class QueueWatchCommandTest extends PHPUnit_Framework_TestCase
{
    /** @var Application */
    private $application;
    /** @var QueueWatchCommand */
    private $command;
    /** @var CommandTester */
    private $commandTester;
    /** @var HypothesisSdk */
    private $hypothesisSdk;
    private $limit;
    private $logger;
    /** @var Monitoring */
    private $monitoring;
    private $transformer;
    /** @var WatchableQueueMock */
    private $queue;

    /**
     * @before
     */
    public function prepareDependencies()
    {
        $this->application = new Application();
        $this->httpClient = $this->getMockBuilder(HttpClient::class)
            ->setMethods(['send'])
            ->getMock();
        $this->hypothesisSdk = new HypothesisSdk($this->httpClient);
        $this->limit = $this->limitIterations(1);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->monitoring = new Monitoring();
        $this->transformer = $this->createMock(QueueItemTransformer::class);
        $this->transformer
            ->expects($this->any())
            ->method('transform')
            ->will($this->returnValue(['field' => 'value']));
        $this->queue = new WatchableQueueMock();
    }

    /**
     * @test
     */
    public function it_will_read_an_item_from_the_queue()
    {
        $this->prepareCommandTester();
        $this->queue->enqueue(new InternalSqsMessage('profile', 'id'));
        $this->assertEquals(1, $this->queue->count());
        $this->commandTesterExecute();
        $this->assertEquals(0, $this->queue->count());
    }

    private function prepareCommandTester($serializedTransform = false)
    {
        $this->command = new QueueWatchCommand($this->queue, $this->transformer, $this->hypothesisSdk, $this->logger, $this->monitoring, $this->limit, $serializedTransform);
        $this->application->add($this->command);
        $this->commandTester = new CommandTester($this->application->get($this->command->getName()));
    }

    private function commandTesterExecute()
    {
        $execArgs = array_filter(
            [
                'command' => $this->command->getName(),
            ]
        );
        $this->commandTester->execute($execArgs);
    }

    private function limitIterations(int $number) : Limit
    {
        $iterationCounter = 0;

        return new CallbackLimit(function () use ($number, &$iterationCounter) {
            ++$iterationCounter;
            if ($iterationCounter > $number) {
                return true;
            }

            return false;
        });
    }
}
