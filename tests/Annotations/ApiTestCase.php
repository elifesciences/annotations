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

    final protected function mockHypothesisSearchCall(
        string $by,
        array $rows,
        int $total,
        int $offset = 1,
        int $limit = 100,
        string $group = '__world__',
        string $order = 'desc',
        string $sort = 'updated'
    ) {
        $json = [
            'total' => $total,
            'rows' => $rows,
        ];

        $this->getMockStorage()->save(
            new Request(
                'GET',
                "https://hypothes.is/api/search?user=$by&group=$group&offset=$offset&limit=$limit&order=$order&sort=$sort",
                []
            ),
            new Response(
                200,
                [],
                json_encode($json)
            )
        );
    }

    final protected function createAnnotations($total = 10) : array
    {
        $annotations = [];
        for ($i = 1; $i <= $total; ++$i) {
            $updated = (rand(0, 1) === 0);
            $text = (rand(0, 3) > 0);
            $highlight = !$text ? true : (rand(0, 3) > 0);
            $parents = rand(0, 3) === 0 ? rand(1, 3) : 0;
            $annotations[] = $this->createAnnotation($i, $updated, $text, $highlight, $parents);
        }

        return $annotations;
    }

    final protected function createAnnotation($id, $updated = true, $text = true, $highlight = true, int $parents = 0) : array
    {
        $created = '2017-12-18T15:11:30.887421+00:00';

        return array_filter([
            'id' => 'identifier'.$id,
            'text' => $text ? 'Annotation text '.$id : null,
            'created' => $created,
            'updated' => $updated ? '2017-12-19T11:13:30.796543+00:00' : $created,
            'document' => [
                'title' => [
                    'Document title',
                ],
            ],
            'target' => array_filter([
                'source' => 'https://elifesciences.org/articles/11860',
                'selector' => $highlight ? [
                    [
                        'type' => 'RangeSelector',
                        'startContainer' => 'div[1]',
                        'endContainer' => 'div[3]',
                        'startOffset' => 5,
                        'endOffset' => 25,
                    ],
                    [
                        'type' => 'TextPositionSelector',
                        'start' => 23609,
                        'end' => 23678,
                    ],
                    [
                        'type' => 'TextQuoteSelector',
                        'exact' => 'Highlighted text '.$id,
                        'prefix' => '',
                        'suffix' => '',
                    ],
                ] : null,
            ]),
            'uri' => 'https://elifesciences.org/articles/11860',
            'references' => array_map(function ($v) {
                static $co = 0;
                ++$co;

                return $v.$co;
            }, array_fill(0, $parents, 'parent')),
            'permissions' => [
                'read' => [
                    'group:__world__',
                ],
            ],
        ]);
    }

    final protected function mockProfileCall(Profile $profile)
    {
        $this->getMockStorage()->save(
            new Request(
                'GET',
                "http://api.elifesciences.org/profiles/{$profile->getId()}",
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
