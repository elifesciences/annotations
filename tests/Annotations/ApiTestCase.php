<?php

namespace tests\eLife\Annotations;

use Csa\Bundle\GuzzleBundle\Cache\StorageAdapterInterface;
use eLife\ApiClient\ApiClient\ProfilesClient;
use eLife\ApiClient\MediaType;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\Profile;
use eLife\ApiValidator\MessageValidator;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use function GuzzleHttp\json_encode;

abstract class ApiTestCase extends TestCase
{
    use HasDiactorosFactory;

    abstract protected function getApiSdk() : ApiSdk;

    abstract protected function getMockStorage() : StorageAdapterInterface;

    abstract protected function getValidator() : MessageValidator;

    final protected function mockNotFound(string $uri, array $headers = [])
    {
        $this->getMockStorage()->save(
            new Request(
                'GET',
                "http://api.elifesciences.org/$uri",
                $headers
            ),
            new Response(
                404,
                ['Content-Type' => 'application/problem+json'],
                json_encode([
                    'title' => 'Not found',
                ])
            )
        );
    }

    final protected function mockAnnotationsCall(
        string $by,

        int $page = 1,
        int $perPage = 100,
        string $order = 'desc',
        string $sort = 'updated',
        string $access = 'public'
    ) {
        $typesQuery = implode('', array_map(function (string $type) {
            return "&type[]=$type";
        }, $types));

        $subjectsQuery = implode('', array_map(function (string $subject) {
            return "&subject[]=$subject";
        }, $subjects));

        $json = [
            'total' => $total,
            'items' => array_map([$this, 'normalize'], $items),
            'subjects' => [],
            'types' => array_reduce([
                'correction',
                'editorial',
                'feature',
                'insight',
                'research-advance',
                'research-article',
                'retraction',
                'registered-report',
                'replication-study',
                'scientific-correspondence',
                'short-report',
                'tools-resources',
                'blog-article',
                'collection',
                'interview',
                'labs-post',
                'podcast-episode',
            ], function (array $carry, string $type) use ($items) {
                $carry[$type] = count(array_filter($items, function (HasIdentifier $model) use ($type) {
                    return $type === $model->getIdentifier();
                }));

                return $carry;
            }, []),
        ];

        $this->getMockStorage()->save(
            new Request(
                'GET',
                "http://api.elifesciences.org/search?for=&page=$page&per-page=$perPage&sort=date&order=desc$subjectsQuery$typesQuery&use-date=default",
                ['Accept' => new MediaType(SearchClient::TYPE_SEARCH, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => new MediaType(SearchClient::TYPE_SEARCH, 1)],
                json_encode($json)
            )
        );
    }

    final protected function mockProfileCall(Profile $profile)
    {
        $this->getMockStorage()->save(
            new Request(
                'GET',
                "http://api.elifesciences.org/podcast-episodes/{$profile->getId()}",
                ['Accept' => new MediaType(ProfilesClient::TYPE_PROFILE, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => new MediaType(ProfilesClient::TYPE_PROFILE, 1)],
                json_encode($this->normalize($profile, false))
            )
        );
    }

    final protected function createProfile(string $id, string $name = null, $orcid = null) : Profile
    {
        $names = array_map('trim', explode(' ', $name ?? 'Jim Bytheway'));
        $preferred = implode(' ', $names);
        $index = array_pop($names);
        if (!empty($names)) {
            $index .= ', '.implode(' ', $names);
        }

        return $this->denormalize(
            [
                'id' => $id,
                'emailAddresses' => [],
                'affiliations' => [],
                'name' => [
                    'index' => $index,
                    'preferred' => $preferred,
                ],
                'orcid' => $orcid ?? '0000-0000-0000-0001',
            ], Profile::class, false);
    }

    final protected function denormalize(array $json, string $type, bool $snippet = true) : Model
    {
        return $this->getApiSdk()->getSerializer()->denormalize($json, $type, 'json', ['snippet' => $snippet, 'type' => $snippet]);
    }

    final protected function normalize(Model $model, bool $snippet = true) : array
    {
        return $this->getApiSdk()->getSerializer()->normalize($model, 'json', ['snippet' => $snippet, 'type' => $snippet]);
    }

    final protected function assertResponseIsValid(HttpFoundationResponse $response)
    {
        $this->assertMessageIsValid($this->getDiactorosFactory()->createResponse($response));
    }

    final protected function assertMessageIsValid(MessageInterface $message)
    {
        $this->getValidator()->validate($message);
    }
}
