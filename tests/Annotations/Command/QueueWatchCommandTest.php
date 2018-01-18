<?php

namespace tests\eLife\Annotations\Command;

use eLife\Annotations\Command\QueueWatchCommand;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\AccessControl;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Profile;
use eLife\Bus\Limit\CallbackLimit;
use eLife\Bus\Limit\Limit;
use eLife\Bus\Queue\InternalSqsMessage;
use eLife\Bus\Queue\Mock\WatchableQueueMock;
use eLife\Bus\Queue\QueueItem;
use eLife\Bus\Queue\QueueItemTransformer;
use eLife\HypothesisClient\ApiSdk as HypothesisSdk;
use eLife\HypothesisClient\Clock\FixedClock;
use eLife\HypothesisClient\Credentials\JWTSigningCredentials;
use eLife\HypothesisClient\Credentials\UserManagementCredentials;
use eLife\HypothesisClient\HttpClient\HttpClient;
use eLife\HypothesisClient\Result\ArrayResult;
use eLife\Logging\Monitoring;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Request;
use PHPUnit_Framework_TestCase;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Debug\BufferingLogger;
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
    /** @var HypothesisSdk */
    private $hypothesisSdk;
    /** @var JWTSigningCredentials */
    private $jwtSigning;
    private $limit;
    /** @var BufferingLogger */
    private $logger;
    /** @var Monitoring */
    private $monitoring;
    private $secretKey;
    private $transformer;
    /** @var UserManagementCredentials */
    private $userManagement;
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
        $this->userManagement = new UserManagementCredentials('client_id', 'secret_key', 'authority');
        $this->jwtSigning = new JWTSigningCredentials('client_id', 'secret_key', 'authority', new FixedClock(1000000000));
        $this->httpClient = $this->getMockBuilder(HttpClient::class)
            ->setMethods(['send'])
            ->getMock();
        $this->hypothesisSdk = new HypothesisSdk($this->httpClient, $this->userManagement, $this->jwtSigning);
        $this->limit = $this->limitIterations(1);
        $this->logger = new BufferingLogger();
        $this->monitoring = new Monitoring();
        $this->transformer = $this->createMock(QueueItemTransformer::class);
        $this->queue = new WatchableQueueMock();
    }

    /**
     * @test
     */
    public function it_will_read_an_item_from_the_queue()
    {
        $this->transformer
            ->expects($this->once())
            ->method('transform')
            ->will($this->returnValue(['foo' => 'bar']));
        $this->prepareCommandTester();
        $this->queue->enqueue(new InternalSqsMessage('profile', 'username'));
        $this->assertEquals(1, $this->queue->count());
        $this->commandTesterExecute();
        $this->assertEquals(0, $this->queue->count());
    }

    /**
     * @test
     * @dataProvider providerProfiles
     */
    public function it_will_process_an_item_in_the_queue(QueueItem $item, Profile $profile, $data, $logs = [])
    {
        $data = [
            'authority' => $this->authority,
        ] + $data;
        $this->transformer
            ->expects($this->once())
            ->method('transform')
            ->with($item)
            ->will($this->returnValue($profile));
        $request = new Request(
            'POST',
            'users',
            ['Authorization' => $this->authorization, 'User-Agent' => 'HypothesisClient'],
            json_encode($data)
        );
        $response = new FulfilledPromise(new ArrayResult($data + ['userid' => 'acct:'.$data['username'].'@test.elifesciences.org']));
        $this->httpClient
            ->expects($this->once())
            ->method('send')
            ->with(RequestConstraint::equalTo($request))
            ->willReturn($response);
        $this->prepareCommandTester();
        $this->queue->enqueue($item);
        $this->assertEquals(1, $this->queue->count());
        $this->commandTesterExecute();
        $this->assertEquals(0, $this->queue->count());
        $actual_logs = $this->logger->cleanLogs();
        $this->assertContains([LogLevel::INFO, 'Hypothesis user "username" successfully created.', []], $actual_logs);
        if (!empty($logs)) {
            foreach ($logs as $log) {
                $this->assertContains($log, $actual_logs);
            }
        }
    }

    public function providerProfiles()
    {
        yield 'standard' => [
            new InternalSqsMessage('profile', 'username'),
            new Profile(
                'username',
                new PersonDetails('PreferredName', 'IndexName'),
                new EmptySequence(),
                new EmptySequence()
            ),
            [
                'username' => 'username',
                'email' => 'username@blackhole.elifesciences.org',
                'display_name' => 'PreferredName',
            ],
            [
                [LogLevel::INFO, 'No email address for profile "username", backup email address created.', []],
            ],
        ];
        yield 'display_name too long' => [
            new InternalSqsMessage('profile', 'username'),
            new Profile(
                'username',
                new PersonDetails('This display name is way too long', 'IndexName'),
                new EmptySequence(),
                new EmptySequence()
            ),
            [
                'username' => 'username',
                'email' => 'username@blackhole.elifesciences.org',
                'display_name' => 'This display name is way too l',
            ],
            [
                [LogLevel::INFO, 'The display name for profile "username" is too long and has been truncated from "This display name is way too long" to "This display name is way too l".', []],
                [LogLevel::INFO, 'No email address for profile "username", backup email address created.', []],
            ],
        ];
        yield 'with single email' => [
            new InternalSqsMessage('profile', 'username'),
            new Profile(
                'username',
                new PersonDetails('PreferredName', 'IndexName'),
                new EmptySequence(),
                new ArraySequence([
                    new AccessControl('username@email.com', AccessControl::ACCESS_PUBLIC),
                ])
            ),
            [
                'username' => 'username',
                'email' => 'username@email.com',
                'display_name' => 'PreferredName',
            ],
        ];
        yield 'with multiple emails' => [
            new InternalSqsMessage('profile', 'username'),
            new Profile(
                'username',
                new PersonDetails('PreferredName', 'IndexName'),
                new EmptySequence(),
                new ArraySequence([
                    new AccessControl('another@email.com', AccessControl::ACCESS_PUBLIC),
                    new AccessControl('username@email.com', AccessControl::ACCESS_PUBLIC),
                ])
            ),
            [
                'username' => 'username',
                'email' => 'another@email.com',
                'display_name' => 'PreferredName',
            ],
        ];
        yield 'with restricted emails (in case authenticated API requests are used)' => [
            new InternalSqsMessage('profile', 'username'),
            new Profile(
                'username',
                new PersonDetails('PreferredName', 'IndexName'),
                new EmptySequence(),
                new ArraySequence([
                    new AccessControl('restricted@email.com', AccessControl::ACCESS_RESTRICTED),
                    new AccessControl('public@email.com', AccessControl::ACCESS_PUBLIC),
                ])
            ),
            [
                'username' => 'username',
                'email' => 'public@email.com',
                'display_name' => 'PreferredName',
            ],
        ];
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
