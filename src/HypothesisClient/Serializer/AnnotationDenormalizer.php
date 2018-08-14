<?php

namespace eLife\HypothesisClient\Serializer;

use Assert\Assert;
use DateTimeImmutable;
use eLife\HypothesisClient\Model\Annotation;
use eLife\HypothesisClient\Model\Annotation\Document;
use eLife\HypothesisClient\Model\Annotation\Permissions;
use eLife\HypothesisClient\Model\Annotation\Target;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class AnnotationDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : Annotation
    {
        if (isset($data['text']) && '' === trim($data['text'])) {
            // Treat annotations of whitespace like page notes
            unset($data['text']);
            unset($data['target'][0]['selector']);
        }

        $data['document'] = $this->denormalizer->denormalize($data['document'], Document::class);
        Assert::that($data['target'])->count(1);
        $data['target'] = $this->denormalizer->denormalize($data['target'][0], Target::class);
        $data['permissions'] = $this->denormalizer->denormalize($data['permissions'], Permissions::class);

        return new Annotation(
            $data['id'],
            $data['text'] ?? null,
            new DateTimeImmutable($data['created']),
            new DateTimeImmutable($data['updated']),
            $data['document'],
            $data['target'],
            $data['uri'],
            $data['references'] ?? [],
            $data['permissions']
        );
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return Annotation::class === $type;
    }
}
