<?php

namespace tests\eLife\HypothesisClient\ApiSdk;

use eLife\HypothesisClient\ApiSdk\ApiSdk;
use eLife\HypothesisClient\ApiSdk\Client\Annotations;

final class ApiSdkTest extends ApiTestCase
{
    /**
     * @var ApiSdk
     */
    private $apiSdk;

    /**
     * @before
     */
    protected function setUpApiSdk()
    {
        $this->apiSdk = new ApiSdk($this->getHttpClient());
    }

    /**
     * @test
     */
    public function it_creates_annotations()
    {
        $this->assertInstanceOf(Annotations::class, $this->apiSdk->annotations());

        $this->mockAnnotationsCall('foo', 'group', 1, 1, 1);
        $this->mockAnnotationsCall('foo', 'group', 1, 100, 1);

        $this->apiSdk->annotations()->get('foo', 'group')->toArray();
    }

    /**
     * @test
     */
    public function it_support_encoding()
    {
        $this->assertTrue($this->apiSdk->getSerializer()->supportsEncoding('json'));
    }

    /**
     * @test
     */
    public function it_support_decoding()
    {
        $this->assertTrue($this->apiSdk->getSerializer()->supportsDecoding('json'));
    }
}
