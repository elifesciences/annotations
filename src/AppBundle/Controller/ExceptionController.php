<?php

namespace eLife\HypothesisClient\AppBundle\Controller;

use eLife\HypothesisClient\AppBundle\Exception\BadRequestHttpException;
use eLife\HypothesisClient\Exception\ApiTimeout;
use eLife\HypothesisClient\Exception\NetworkProblem;
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

        if (Response::HTTP_NOT_FOUND === $statusCode) {
            $response = new Response(json_encode(['title' => $exception->getMessage()]), $exception->getStatusCode());
        } elseif (in_array($exception->getClass(), [ApiTimeout::class, NetworkProblem::class])) {
            $response = new Response(json_encode(['title' => 'Service unavailable']), Response::HTTP_GATEWAY_TIMEOUT);
        } elseif (BadRequestHttpException::class !== $exception->getClass()) {
            $response = new Response(json_encode(['title' => 'Service unavailable '.get_class($exception)]), Response::HTTP_BAD_GATEWAY);
        } else {
            $response = new Response($exception->getMessage(), $statusCode);
        }

        $response->headers->add(['Content-Type' => 'application/problem+json']);
        $response->setMaxAge($this->getParameter('ttl'));
        $response->headers->addCacheControlDirective('stale-while-revalidate', $this->getParameter('ttl'));
        $response->headers->addCacheControlDirective('stale-if-error', 86400);
        $response->setVary('Accept');
        $response->setEtag(md5($response->getContent()));
        $response->setPublic();
        $response->isNotModified($request);

        return $response;
    }
}
