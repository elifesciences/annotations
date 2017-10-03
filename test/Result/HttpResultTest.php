<?php

namespace test\eLife\HypothesisClient\Result;

use eLife\HypothesisClient\Result\HttpResult;
use GuzzleHttp\Psr7\Response;
use PHPUnit_Framework_TestCase;
use TypeError;
use UnexpectedValueException;

final class HttpResultTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_requires_a_http_response()
    {
        $this->expectException(TypeError::class);

        HttpResult::fromResponse('foo');
    }

    /**
     * @test
     */
    public function it_requires_data()
    {
        $this->expectException(UnexpectedValueException::class);

        HttpResult::fromResponse(new Response(200));
    }

    /**
     * @test
     */
    public function it_requires_json_data()
    {
        $this->expectException(UnexpectedValueException::class);

        HttpResult::fromResponse(new Response(200, [], 'foo'));
    }
}
