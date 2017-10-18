<?php

namespace tests\eLife\HypothesisClient\AppBundle\EventListener;

use eLife\HypothesisClient\AppBundle\EventListener\NetworkProblemSubscriber;
use eLife\HypothesisClient\Exception\ApiException;
use eLife\HypothesisClient\Exception\NetworkProblem;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class NetworkProblemTimeoutSubscriberTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_turns_network_problem_into_a_504_exception()
    {
        $subscriber = new NetworkProblemSubscriber();

        $exception = new NetworkProblem('Network problem', new GuzzleRequest('GET', 'http://www.example.com/'));
        $event = new GetResponseForExceptionEvent($this->createMock(HttpKernelInterface::class), new Request(), HttpKernelInterface::MASTER_REQUEST, $exception);

        $subscriber->onKernelException($event);

        $this->assertEquals(new HttpException(504, 'Network problem', $exception), $event->getException());
    }

    /**
     * @test
     */
    public function it_ignores_other_exceptions()
    {
        $subscriber = new NetworkProblemSubscriber();

        $exception = new ApiException('Exception');
        $event = new GetResponseForExceptionEvent($this->createMock(HttpKernelInterface::class), new Request(), HttpKernelInterface::MASTER_REQUEST, $exception);

        $subscriber->onKernelException($event);

        $this->assertSame($exception, $event->getException());
    }
}
