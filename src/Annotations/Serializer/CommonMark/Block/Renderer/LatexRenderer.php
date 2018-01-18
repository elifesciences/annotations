<?php

namespace eLife\Annotations\Serializer\CommonMark\Block\Renderer;

use eLife\Annotations\Serializer\CommonMark\Block\Element\Latex;
use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Renderer\BlockRendererInterface;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\Util\Xml;

class LatexRenderer implements BlockRendererInterface
{
    public function render(AbstractBlock $block, ElementRendererInterface $htmlRenderer, $inTightList = false)
    {
        if (!($block instanceof Latex)) {
            throw new \InvalidArgumentException('Incompatible block type: '.get_class($block));
        }

        $rendered = $htmlRenderer->renderInlines($block->children());
        $rendered = preg_replace_callback('~(?P<before>\$\$)(?P<latex>.*)(?P<after>\$\$)~m', function ($match) {
            return $match['before'].base64_decode($match['latex']).$match['after'];
        }, $rendered);

        return Xml::escape($rendered);
    }
}
