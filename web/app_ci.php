<?php

use eLife\Annotations\AppKernel;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__.'/../vendor/autoload.php';

$app = new AppKernel('ci');

$request = Request::createFromGlobals();

$response = $app->handle($request);
$response->send();

$app->terminate($request, $response);