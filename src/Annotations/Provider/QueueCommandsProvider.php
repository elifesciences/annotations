<?php

namespace eLife\Annotations\Provider;

use eLife\Annotations\Command\QueueCreateCommand;
use eLife\Annotations\Command\QueueImportCommand;
use eLife\Annotations\Command\QueuePushCommand;
use eLife\Annotations\Command\QueueWatchCommand;
use eLife\Bus\Command\QueueCleanCommand;
use eLife\Bus\Command\QueueCountCommand;
use Knp\Console\Application;
use LogicException;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use ReflectionClass;

class QueueCommandsProvider implements ServiceProviderInterface
{
    /**
     * Registers the annotations service console commands.
     *
     * @param Container $container
     */
    public function register(Container $container)
    {
        if (!isset($container['console'])) {
            throw new LogicException(sprintf('You must register the ConsoleServiceProvider to use the %s.', (new ReflectionClass(self::class))->getShortName()));
        }

        $container->extend('console', function (Application $console) use ($container) {
            $console->add(new QueueCleanCommand($container['aws.queue'], $container['logger']));
            $console->add(new QueueCountCommand($container['aws.queue']));
            $console->add(new QueuePushCommand($container['aws.queue'], $container['logger'], $container['sqs.queue_message_type'] ?? null));
            $console->add(new QueueCreateCommand($container['aws.sqs'], $container['logger'], $container['sqs.queue_name'] ?? null, $container['sqs.region'] ?? null));
            $console->add(new QueueImportCommand(
                $container['api.sdk'],
                $container['aws.queue'],
                $container['logger'],
                $container['monitoring'],
                $container['limit.interactive']
            ));
            $console->add(new QueueWatchCommand(
                $container['aws.queue'],
                $container['aws.queue_transformer'],
                $container['hypothesis.sdk'],
                $container['logger'],
                $container['monitoring'],
                $container['limit.long_running']
            ));

            return $console;
        });
    }
}
