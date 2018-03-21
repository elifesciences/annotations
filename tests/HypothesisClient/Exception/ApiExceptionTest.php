<?php

namespace tests\eLife\HypothesisClient\Exception;

use eLife\HypothesisClient\Exception\ApiException;
use Exception;
use PHPUnit_Framework_TestCase;
use RuntimeException;

/**
 * @covers \eLife\HypothesisClient\Exception\ApiException
 */
class ApiExceptionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_requires_a_message()
    {
        $e = new ApiException('foo');
        $this->assertSame('foo', $e->getMessage());
    }

    /**
     * @test
     */
    public function it_has_an_error_code_of_zero()
    {
        $e = new ApiException('foo');
        $this->assertSame(0, $e->getCode());
    }

    /**
     * @test
     */
    public function it_is_an_instance_of_runtime_exception()
    {
        $e = new ApiException('foo');
        $this->assertInstanceOf(RuntimeException::class, $e);
    }

    /**
     * @test
     */
    public function it_may_not_have_a_previous_exception()
    {
        $e = new ApiException('foo');
        $this->assertNull($e->getPrevious());
    }

    /**
     * @test
     */
    public function it_may_have_a_previous_exception()
    {
        $previous = new Exception('bar');
        $e = new ApiException('foo', $previous);
        $this->assertSame($previous, $e->getPrevious());
    }
}
