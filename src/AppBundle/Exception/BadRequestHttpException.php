<?php

namespace eLife\HypothesisClient\AppBundle\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BadRequestHttpException extends HttpException
{
    public function __construct($message = null, \Exception $previous = null, $media_type = 'application/problem+json', $code = 0)
    {
        parent::__construct(Response::HTTP_BAD_REQUEST, $message, $previous, ['Content-Type' => $media_type], $code);
    }
}
