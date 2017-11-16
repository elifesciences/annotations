<?php

namespace tests\eLife\Annotations\Provider;

use Aws\Sqs\SqsClient;
use eLife\Annotations\Provider\QueueCommandsProvider;
use eLife\ApiClient\HttpClient;
use eLife\ApiSdk\ApiSdk;
use eLife\Bus\Limit\Limit;
use eLife\Bus\Queue\QueueItemTransformer;
use eLife\Bus\Queue\WatchableQueue;
use eLife\HypothesisClient\ApiSdk as HypothesisApiSdk;
use eLife\HypothesisClient\HttpClient\HttpClient as HypothesisHttpClient;
use eLife\Logging\Monitoring;
use Knp\Console\Application as ConsoleApplication;
use Knp\Provider\ConsoleServiceProvider;
use LogicException;
use PHPUnit_Framework_TestCase;
use Psr\Log\LoggerInterface;
use Silex\Application;

/**
 * @covers \eLife\Annotations\Provider\QueueCommandsProvider
 */
final class QueueCommandsProviderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Application
     */
    private $app;
    private $container = [];
    private $httpClient;
    private $sqs;
    private $queue;
    private $logger;

    /**
     * @before
     */
    public function prepareContainers()
    {
        $this->app = new Application();
        $this->httpClient = $this->getMockBuilder(HttpClient::class)
            ->setMethods(['send'])
            ->getMock();
        $this->sqs = $this->getMockBuilder(SqsClient::class)
            ->disableOriginalConstructor()
            ->setMethods(['getQueueUrl', 'createQueue'])
            ->getMock();
        $this->queue = $this->getMockBuilder(WatchableQueue::class)
            ->setMethods(['enqueue', 'dequeue', 'commit', 'release', 'clean', 'getName', 'count'])
            ->getMock();
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->setMethods(['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug', 'log'])
            ->getMock();
        $this->container = [
            'api.sdk' => new ApiSdk($this->httpClient),
            'hypothesis.sdk' => new HypothesisApiSdk($this->createMock(HypothesisHttpClient::class)),
            'limit.interactive' => $this->app->protect($this->createMock(Limit::class)),
            'limit.long_running' => $this->app->protect($this->createMock(Limit::class)),
            'logger' => $this->logger,
            'monitoring' => new Monitoring(),
            'aws.sqs' => $this->sqs,
            'aws.queue' => $this->queue,
            'aws.queue_transformer' => $this->createMock(QueueItemTransformer::class),
        ];
    }

    /**
     * @test
     */
    public function commands_are_registered()
    {
        $this->app->register(new ConsoleServiceProvider());
        $this->app->register(new QueueCommandsProvider(), $this->container);
        /** @var ConsoleApplication $console */
        $console = $this->app['console'];
        $this->assertTrue($console->has('queue:count'));
        $this->assertTrue($console->has('queue:clean'));
        $this->assertTrue($console->has('queue:create'));
        $this->assertTrue($console->has('queue:import'));
        $this->assertTrue($console->has('queue:push'));
        $this->assertTrue($console->has('queue:watch'));
    }

    /**
     * @test
     */
    public function registration_fails_if_no_console_provider()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('You must register the ConsoleServiceProvider to use the QueueCommandsProvider');
        $this->app->register(new QueueCommandsProvider(), $this->container);
    }
}
