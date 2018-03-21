<?php

namespace tests\eLife\Annotations\Command;

use eLife\Annotations\Command\QueueImportCommand;
use eLife\Annotations\Command\QueuePushCommand;
use eLife\Bus\Queue\InternalSqsMessage;
use eLife\Bus\Queue\Mock\WatchableQueueMock;
use PHPUnit_Framework_TestCase;
use Psr\Log\NullLogger;
use RuntimeException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @covers \eLife\Annotations\Command\QueuePushCommand
 */
class QueuePushCommandTest extends PHPUnit_Framework_TestCase
{
    /** @var Application */
    private $application;
    /** @var QueueImportCommand */
    private $command;
    /** @var CommandTester */
    private $commandTester;
    private $logger;
    /** @var WatchableQueueMock */
    private $queue;

    /**
     * @before
     */
    public function prepareDependencies()
    {
        $this->application = new Application();
        $this->logger = new NullLogger();
        $this->queue = new WatchableQueueMock();
    }

    /**
     * @test
     */
    public function it_will_push_to_the_queue()
    {
        $this->prepareCommandTester();
        $this->assertEmpty($this->queue->count());
        $this->commandTesterExecute('id', 'profiles');
        $this->assertEquals(1, $this->queue->count());
        $this->assertEquals(new InternalSqsMessage('profiles', 'id'), $this->queue->dequeue());
    }

    /**
     * @test
     */
    public function it_requires_an_id()
    {
        $this->prepareCommandTester();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments (missing: "id").');
        $this->commandTesterExecute(null, 'profiles');
    }

    /**
     * @test
     */
    public function it_requires_a_type()
    {
        $this->prepareCommandTester();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments (missing: "type").');
        $this->commandTesterExecute('id', null);
    }

    /**
     * @test
     */
    public function it_may_have_a_default_type()
    {
        $this->prepareCommandTester('defaultType');
        $this->commandTesterExecute('id', null);
        $this->assertEquals(new InternalSqsMessage('defaultType', 'id'), $this->queue->dequeue());
    }

    /**
     * @test
     */
    public function it_may_override_the_default_type()
    {
        $this->prepareCommandTester('defaultType');
        $this->commandTesterExecute('id', 'overrideType');
        $this->assertEquals(new InternalSqsMessage('overrideType', 'id'), $this->queue->dequeue());
    }

    private function prepareCommandTester($type = null)
    {
        $this->command = new QueuePushCommand($this->queue, $this->logger, $type);
        $this->application->add($this->command);
        $this->commandTester = new CommandTester($this->application->get($this->command->getName()));
    }

    private function commandTesterExecute($id, $type)
    {
        $execArgs = array_filter(
            [
                'command' => $this->command->getName(),
                'id' => $id,
                'type' => $type,
            ]
        );
        $this->commandTester->execute($execArgs);
    }
}
