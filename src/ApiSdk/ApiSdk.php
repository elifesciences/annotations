<?php

namespace eLife\HypothesisClient\ApiSdk;

use eLife\HypothesisClient\ApiClient\AnnotationsClient;
use eLife\HypothesisClient\HttpClient;
use eLife\HypothesisClient\HttpClient\UserAgentPrependingHttpClient;
use eLife\HypothesisClient\ApiSdk\Client\Annotations;
use eLife\HypothesisClient\ApiSdk\Serializer\AnnotationNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

final class ApiSdk
{

    private $httpClient;
    private $annotationsClient;
    private $serializer;
    private $annotations;

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = new UserAgentPrependingHttpClient($httpClient, 'HypothesisClientApiSdk');
        $this->annotationsClient = new AnnotationsClient($this->httpClient);

        $this->serializer = new Serializer([
            new AnnotationNormalizer(),
        ], [new JsonEncoder()]);
    }

    public function annotations() : Annotations
    {
        if (empty($this->annotations)) {
            $this->annotations = new Annotations(new AnnotationsClient($this->httpClient), $this->serializer);
        }

        return $this->annotations;
    }

    public function getSerializer() : Serializer
    {
        return $this->serializer;
    }
}
