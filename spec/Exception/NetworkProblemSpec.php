<?php

namespace spec\eLife\HypothesisClient\Exception;

use eLife\HypothesisClient\Exception\HttpProblem;
use Exception;
use GuzzleHttp\Psr7\Request;
use PhpSpec\ObjectBehavior;

final class NetworkProblemSpec extends ObjectBehavior
{
    private $message;
    private $request;

    public function let()
    {
        $this->message = 'foo';
        $this->request = new Request(200, '/');

        $this->beConstructedWith($this->message, $this->request);
    }

    public function it_has_a_message()
    {
        $this->getMessage()->shouldBe($this->message);
    }

    public function it_has_a_request()
    {
        $this->getRequest()->shouldBeLike($this->request);
    }

    public function it_can_not_have_a_previous_exception()
    {
        $this->getPrevious()->shouldBe(null);
    }

    public function it_can_have_a_previous_exception(Exception $previous)
    {
        $this->beConstructedWith($this->message, $this->request, $previous);

        $this->getPrevious()->shouldBeLike($previous);
    }

    public function it_is_a_http_problem()
    {
        $this->shouldHaveType(HttpProblem::class);
    }
}
