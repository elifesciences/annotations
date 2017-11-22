<?php

namespace tests\eLife\Annotations\Command;

use eLife\Annotations\Command\QueueWatchCommand;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Profile;
use eLife\Bus\Limit\CallbackLimit;
use eLife\Bus\Limit\Limit;
use eLife\Bus\Queue\InternalSqsMessage;
use eLife\Bus\Queue\Mock\WatchableQueueMock;
use eLife\Bus\Queue\QueueItemTransformer;
use eLife\HypothesisClient\ApiSdk as HypothesisSdk;
use eLife\HypothesisClient\Credentials\Credentials;
use eLife\HypothesisClient\HttpClient\HttpClient;
use eLife\HypothesisClient\Result\ArrayResult;
use eLife\Logging\Monitoring;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Request;
use PHPUnit_Framework_TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use tests\eLife\HypothesisClient\RequestConstraint;

/**
 * @covers \eLife\Annotations\Command\QueueWatchCommand
 */
class QueueWatchCommandTest extends PHPUnit_Framework_TestCase
{
    /** @var Application */
    private $application;
    private $authority;
    private $authorization;
    private $clientId;
    /** @var QueueWatchCommand */
    private $command;
    /** @var CommandTester */
    private $commandTester;
    /** @var Credentials */
    private $credentials;
    /** @var HypothesisSdk */
    private $hypothesisSdk;
    private $limit;
    private $logger;
    /** @var Monitoring */
    private $monitoring;
    private $secretKey;
    private $transformer;
    /** @var WatchableQueueMock */
    private $queue;

    /**
     * @before
     */
    public function prepareDependencies()
    {
        $this->application = new Application();
        $this->clientId = 'client_id';
        $this->secretKey = 'secret_key';
        $this->authority = 'authority';
        $this->authorization = sprintf('Basic %s', base64_encode($this->clientId.':'.$this->secretKey));
        $this->credentials = new Credentials('client_id', 'secret_key', 'authority');
        $this->httpClient = $this->getMockBuilder(HttpClient::class)
            ->setMethods(['send'])
            ->getMock();
        $this->hypothesisSdk = new HypothesisSdk($this->httpClient, $this->credentials);
        $this->limit = $this->limitIterations(1);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->monitoring = new Monitoring();
        $this->transformer = $this->createMock(QueueItemTransformer::class);
        $data = [
            'authority' => 'authority',
            'username' => 'username',
            'email' => 'username@hypothesis.elifesciences.org',
            'display_name' => 'Preferred Name',
        ];
        $profile = new Profile($data['username'], new PersonDetails($data['display_name'], 'IndexName'), new ArraySequence([]), new ArraySequence([]));
        $this->transformer
            ->expects($this->any())
            ->method('transform')
            ->will($this->returnValue($profile));
        $request = new Request(
            'POST',
            'users',
            ['Authorization' => $this->authorization, 'User-Agent' => 'HypothesisClient'],
            json_encode($data)
        );
        $response = new FulfilledPromise(new ArrayResult($data));
        $this->httpClient
            ->expects($this->once())
            ->method('send')
            ->with(RequestConstraint::equalTo($request))
            ->willReturn($response);
        $this->queue = new WatchableQueueMock();
    }

    /**
     * @test
     */
    public function it_will_read_an_item_from_the_queue()
    {
        $this->prepareCommandTester();
        $this->queue->enqueue(new InternalSqsMessage('profile', 'username'));
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
