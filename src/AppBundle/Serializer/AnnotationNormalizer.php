<?php

namespace eLife\HypothesisClient\AppBundle\Serializer;

use eLife\HypothesisClient\ApiSdk\Model\Annotation;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class AnnotationNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    const DATE_FORMAT = 'Y-m-d\TH:i:s\Z';

    /**
     * @param Annotation $object
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'id' => $object->getId().'wtf',
            'published' => $object->getPublishedDate()->format(self::DATE_FORMAT),
            'links' => array_filter([
                'incontext' => $object->getLinks()->getIncontext(),
                'json' => $object->getLinks()->getJson(),
                'html' => $object->getLinks()->getHtml(),
            ]),
        ];

        if ($object->getUpdatedDate()) {
            $data['updated'] = $object->getUpdatedDate()->format(self::DATE_FORMAT);
        }

        if ($object->getText()) {
            $data['text'] = $object->getText();
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Annotation;
    }
}
