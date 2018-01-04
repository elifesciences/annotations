<?php

namespace tests\eLife\Annotations;

use Csa\Bundle\GuzzleBundle\Cache\StorageAdapterInterface;
use eLife\Annotations\AppKernel;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiValidator\MessageValidator;
use function GuzzleHttp\json_encode;

abstract class ApplicationTestCase extends ApiTestCase
{
    /** @var AppKernel */
    private $app;

    /**
     * @before
     */
    final public function setUpApp()
    {
        $this->app = new AppKernel('test');
    }

    final protected function getApp() : AppKernel
    {
        return $this->app;
    }

    final protected function getApiSdk() : ApiSdk
    {
        return $this->app->get('api.sdk');
    }

    final protected function getMockStorage() : StorageAdapterInterface
    {
        return $this->app->get('guzzle.mock.in_memory_storage');
    }

    final protected function getValidator() : MessageValidator
    {
        return $this->app->get('elife.json_message_validator');
    }

    final protected function assertJsonStringEqualsJson(array $expectedJson, string $actualJson, $message = '')
    {
        $this->assertJsonStringEqualsJsonString(json_encode($expectedJson), $actualJson, $message);
    }
}
