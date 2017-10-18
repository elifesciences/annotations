<?php

namespace eLife\HypothesisClient\AppBundle\Controller;

use eLife\HypothesisClient\ApiSdk\Collection\PromiseSequence;
use eLife\HypothesisClient\ApiSdk\Model\Annotation;
use eLife\HypothesisClient\AppBundle\Exception\BadRequestHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AnnotationsController extends Controller
{
    public function getAction(Request $request)
    {
        $by = $request->query->get('by');
        $page = (int) $request->query->get('page', 1);
        $perPage = (int) $request->query->get('per-page', 10);
        $order = $request->query->get('order', 'desc');

        // Check if the page filter is valid.
        if ($page < 1) {
            throw new BadRequestHttpException('Invalid page option');
        }

        // Check if the per-page filter is valid.
        if ($perPage < 1 || $perPage > 100) {
            throw new BadRequestHttpException('Invalid per-page option');
        }

        // Check if the order filter is valid.
        if (!in_array(strtolower($order), ['asc', 'desc'])) {
            throw new BadRequestHttpException('Invalid order option');
        }

        // Check if the by[] filter is valid.
        if (empty($by)) {
            throw new BadRequestHttpException('Missing by[] option');
        } elseif (
            !is_array($by) ||
            !empty(array_filter($by, function ($v) {
                return !preg_match('/^[A-Za-z0-9._]{3,30}$/', (string) $v);
            }))
        ) {
            throw new BadRequestHttpException('Invalid by[] option');
        } else {
            // Only use the first by[] filter until hypothes.is can support multiple in a single request.
            $by = reset($by);
        }

        $annotations = $this->get('elife.hypothesis_client.api_sdk.annotations')
            ->get($by, $this->getParameter('hypothesis_api_publisher'));

        // Reverse order if asc is requested.
        if ('asc' === strtolower($order)) {
            $annotations = $annotations->reverse();
        }

        $serializer = $this->get('elife.hypothesis_client.api_sdk.annotations.serializer');

        $list = (new PromiseSequence($annotations
            ->slice(($page - 1) * $perPage, $perPage)))
            ->map(function (Annotation $annotation) use ($serializer) {
                return $serializer->normalize($annotation);
            });

        // Preparing the list before the count() saves a request.
        $rows = $list->toArray();
        $total = $annotations->count();
        $response = new Response(json_encode(['total' => $total, 'rows' => $rows]));
        $response->headers->add(['Content-Type' => 'application/vnd.elife.annotation-list+json;version=1']);

        return $response;
    }
}
