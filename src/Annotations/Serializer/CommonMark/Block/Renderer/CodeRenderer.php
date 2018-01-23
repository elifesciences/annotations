<?php

namespace eLife\Annotations\Serializer\CommonMark\Block\Renderer;

use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Element\FencedCode;
use League\CommonMark\Block\Element\IndentedCode;
use League\CommonMark\Block\Renderer\BlockRendererInterface;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\Util\Xml;

class CodeRenderer implements BlockRendererInterface
{
    public function render(AbstractBlock $block, ElementRendererInterface $htmlRenderer, $inTightList = false)
    {
        if (!($block instanceof FencedCode) && !($block instanceof IndentedCode)) {
            throw new \InvalidArgumentException('Incompatible block type: '.get_class($block));
        }

        return Xml::escape(trim($block->getStringContent()));
    }
}
