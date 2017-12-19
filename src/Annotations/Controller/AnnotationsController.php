<?php

namespace eLife\Annotations\Controller;

use eLife\Annotations\ApiResponse;
use eLife\ApiClient\Exception\ApiProblemResponse;
use eLife\ApiSdk\ApiSdk;
use eLife\HypothesisClient\ApiSdk as HypothesisSdk;
use eLife\HypothesisClient\Model\Annotation;
use eLife\HypothesisClient\Model\Token;
use Negotiation\Accept;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use function GuzzleHttp\Psr7\normalize_header;

final class AnnotationsController
{
    private $apiSdk;
    private $hypothesisSdk;

    public function __construct(HypothesisSdk $hypothesisSdk, ApiSdk $apiSdk)
    {
        $this->apiSdk = $apiSdk;
        $this->hypothesisSdk = $hypothesisSdk;
    }

    public function annotationsAction(Request $request, Accept $type) : Response
    {
        // Retrieve query parameters.
        $by = $request->query->get('by');
        $page = $request->query->get('page', 1);
        $perPage = $request->query->get('per-page', 20);
        $order = $request->query->get('order', 'desc');
        $useDate = $request->query->get('use-date', 'updated');
        $access = $request->query->get('access', 'public');

        // Retrieve consumer groups.
        $groups = normalize_header($request->headers->get('X-Consumer-Groups', 'user'));

        // Set access options.
        $accessOptions = [
            'public',
        ];

        // Only allow access to restricted annotations if in the appropriate consumer group.
        if (in_array('view-restricted-annotations', $groups)) {
            $accessOptions[] = 'restricted';
        }

        // Verify that by parameter is present.
        if (!is_string($by) || empty($by)) {
            throw new BadRequestHttpException('Missing by value');
        }

        // Verify that order parameter is valid.
        if (!in_array($order, ['asc', 'desc'])) {
            throw new BadRequestHttpException('Invalid order value: asc or desc expected');
        }

        // Verify that page parameter is valid.
        if ($page != (int) $page || $page < 1) {
            throw new NotFoundHttpException('Invalid page value');
        }

        // Verify that per-page parameter is valid.
        if ($perPage != (int) $perPage || $perPage < 1 || ($perPage > 100)) {
            throw new NotFoundHttpException('Invalid per-page value: 1...100 expected');
        }

        // Varify that use-date parameter is valid.
        if (!in_array($useDate, ['updated', 'created'])) {
            throw new BadRequestHttpException('Invalid use-date value: updated or created expected');
        }

        // Verify that access parameter is valid.
        if (!in_array($access, $accessOptions)) {
            throw new BadRequestHttpException('Invalid access value: '.implode(' or ', $accessOptions).' expected');
        }

        // Retrieve access token, if appropriate.
        if ('restricted' === $access) {
            $accessToken = $this->hypothesisSdk->token()->get($by)
                ->then(function (Token $token) {
                    return $token->getAccessToken();
                })->wait();
        } else {
            $accessToken = null;
        }

        // Perform query to Hypothesis API.
        $content = $this->hypothesisSdk->search()->query($by, $accessToken, ($page-1)*$perPage, $perPage, ('desc' === $order), ('updated' === $useDate))
            ->then(function (array $result) {
                return [
                    'total' => $this->hypothesisSdk->search()->count(),
                    'items' => array_map(function (Annotation $annotation) {
                        $item = array_filter([
                            'id' => $annotation->getId(),
                            'access' => ($annotation->getPermissions()->getRead() === 'group:__world__') ? 'public' : 'restricted',
                            'content' => $annotation->getText(),
                            'parents' => $annotation->getReferences(),
                            'created' => $annotation->getCreatedDate()->format(ApiSdk::DATE_FORMAT),
                            'updated' => $annotation->getCreatedDate()->format(ApiSdk::DATE_FORMAT),
                            'document' => [
                                'title' => $annotation->getDocument()->getTitle(),
                                'uri' => $annotation->getUri(),
                            ],
                        ]) + ['parents' => []];
                        if ($item['created'] === $item['updated']) {
                            unset($item['updated']);
                        }
                        if ($annotation->getTarget()->getSelector()) {
                            $item['highlight'] = $annotation->getTarget()->getSelector()->getTextQuote()->getExact();
                        }
                        return $item;
                    }, $result),
                ];
            })->wait();

        // Verify that page is in the correct range.
        if (0 === count($content['items']) && $page > 1) {
            throw new NotFoundHttpException('No page '.$page);
        // If no results found, ensure that profile exists.
        } elseif (1 == $page && 0 === count($content['items'])) {
            $this->apiSdk->profiles()->get($by)
                ->otherwise(function ($reason) use ($by) {
                    if ($reason instanceof ApiProblemResponse && Response::HTTP_NOT_FOUND === $reason->getResponse()->getStatusCode()) {
                        throw new NotFoundHttpException('Unknown profile: '.$by);
                    }
                })->wait();
        }

        // Set Content-Type.
        $headers = ['Content-Type' => $type->getNormalizedValue()];

        return new ApiResponse(
            $content,
            Response::HTTP_OK,
            $headers
        );
    }
}
