<?php

namespace eLife\HypothesisClient\ApiSdk\Client;

use eLife\HypothesisClient\ApiClient\AnnotationsClient;
use eLife\HypothesisClient\Result;
use eLife\HypothesisClient\ApiSdk\Collection\PromiseSequence;
use eLife\HypothesisClient\ApiSdk\Collection\Sequence;
use eLife\HypothesisClient\ApiSdk\Model\Annotation;
use Iterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class Annotations
{
    private $annotationsClient;
    private $denormalizer;

    public function __construct(AnnotationsClient $annotationsClient, DenormalizerInterface $denormalizer)
    {
        $this->annotationsClient = $annotationsClient;
        $this->denormalizer = $denormalizer;
    }

    public function get(string $user, string $group = '__world__') : Sequence
    {
        $annotationsClient = $this->annotationsClient;
        $denormalizer = $this->denormalizer;

        return new class($annotationsClient, $denormalizer, $user, $group) implements Iterator, Sequence {
            use Client;

            private $count;
            private $descendingOrder = true;
            private $annotationsClient;
            private $denormalizer;
            private $user;
            private $group;

            public function __construct(AnnotationsClient $annotationsClient, DenormalizerInterface $denormalizer, string $user, string $group)
            {
                $this->annotationsClient = $annotationsClient;
                $this->denormalizer = $denormalizer;
                $this->user = $user;
                $this->group = $group;
            }

            public function slice(int $offset, int $length = null) : Sequence
            {
                if (null === $length) {
                    return new PromiseSequence($this->all()
                        ->then(function (Sequence $sequence) use ($offset) {
                            return $sequence->slice($offset);
                        })
                    );
                }

                return new PromiseSequence($this->annotationsClient
                    ->listAnnotations(
                        [],
                        $this->user,
                        ($offset / $length) + 1,
                        $length,
                        $this->descendingOrder,
                        $this->group
                    )
                    ->then(function (Result $result) {
                        $this->count = $result['total'];

                        return $result;
                    })
                    ->then(function (Result $result) {
                        return array_map(function (array $annotation) {
                            return $this->denormalizer->denormalize($annotation, Annotation::class);
                        }, $result['rows']);
                    })
                );
            }

            public function reverse() : Sequence
            {
                $clone = clone $this;

                $clone->descendingOrder = !$this->descendingOrder;

                return $clone;
            }
        };
    }
}