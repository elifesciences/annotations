<?php

namespace eLife\HypothesisClient\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;

final class ExceptionController extends Controller
{
    public function showAction(Request $request, FlattenException $exception, DebugLoggerInterface $logger = null) : Response
    {
        if ($request->attributes->get('showException', $this->get('kernel')->isDebug())) {
            return $this->get('twig.controller.exception')->showAction($request, $exception, $logger);
        }

        // Cast integers represented as strings to integer. This is required because the status code is a string when previewing the error pages (e.g. /_error/404)
        $statusCode = ($exception->getStatusCode() == (int) $exception->getStatusCode()) ? (int) $exception->getStatusCode() : $exception->getStatusCode();

        if (in_array($statusCode, [Response::HTTP_OK, Response::HTTP_NOT_FOUND, Response::HTTP_GATEWAY_TIMEOUT, Response::HTTP_BAD_REQUEST])) {
            $response = new Response(json_encode(['title' => $exception->getMessage()]), $statusCode);
        } else {
            $response = new Response(json_encode(['title' => 'Service unavailable']), Response::HTTP_BAD_GATEWAY);
        }

        $response->headers->add(['Content-Type' => 'application/problem+json']);

        return $response;
    }
}
