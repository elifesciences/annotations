<?php

namespace tests\eLife\Annotations;

use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;

trait HasDiactorosFactory
{
    final private function getDiactorosFactory() : DiactorosFactory
    {
        return new DiactorosFactory();
    }
}
