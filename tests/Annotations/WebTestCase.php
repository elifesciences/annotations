<?php

namespace tests\eLife\Annotations;

use eLife\Annotations\AppKernel;
use Silex\WebTestCase as SilexWebTestCase;
use Symfony\Component\HttpKernel\HttpKernelInterface;

abstract class WebTestCase extends SilexWebTestCase
{
    /** @var AppKernel */
    protected $kernel;

    public function createApplication() : HttpKernelInterface
    {
        $this->kernel = new AppKernel('test');

        return $this->kernel;
    }
}
