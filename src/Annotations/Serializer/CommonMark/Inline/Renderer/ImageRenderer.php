<?php

namespace eLife\Annotations\Serializer\CommonMark\Inline\Renderer;

use League\CommonMark\ElementRendererInterface;
use League\CommonMark\Inline\Element\AbstractInline;
use League\CommonMark\Inline\Element\Image;
use League\CommonMark\Inline\Renderer\ImageRenderer as CommonMarkImageRenderer;
use League\CommonMark\Util\RegexHelper;
use League\CommonMark\Util\Xml;

class ImageRenderer extends CommonMarkImageRenderer
{
    /**
     * {@inheritdoc}
     */
    public function render(AbstractInline $inline, ElementRendererInterface $htmlRenderer)
    {
        if (!($inline instanceof Image)) {
            throw new \InvalidArgumentException('Incompatible inline type: '.get_class($inline));
        }

        $forbidUnsafeLinks = $this->config->getConfig('safe') || !$this->config->getConfig('allow_unsafe_links');
        if ($forbidUnsafeLinks && RegexHelper::isLinkPotentiallyUnsafe($inline->getUrl())) {
            return '';
        } else {
            return Xml::escape($inline->getUrl(), true);
        }
    }
}
