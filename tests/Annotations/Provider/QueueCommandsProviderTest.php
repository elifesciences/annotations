<?php

namespace tests\eLife\Annotations\Provider;

use eLife\Annotations\Provider\QueueCommandsProvider;
use eLife\Bus\Queue\Mock\WatchableQueueMock;
use LogicException;
use Silex\Application;
use tests\eLife\Annotations\WebTestCase;

/**
 * @covers \eLife\Annotations\Provider\QueueCommandsProvider
 */
final class QueueCommandsProviderTest extends WebTestCase
{
    /**
     * @before
     */
    public function setupSqs()
    {
        $this->kernel->override('aws.queue', function () {
            return new WatchableQueueMock();
        });
    }

    /**
     * @test
     */
    public function commands_are_registered()
    {
        $console = $this->kernel->get('console');
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
        $application = new Application();
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('You must register the ConsoleServiceProvider to use the QueueCommandsProvider');
        $application->register(new QueueCommandsProvider());
    }
}
