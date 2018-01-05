<?php

namespace eLife\Annotations\Renderer\Block;

use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Element\FencedCode;
use League\CommonMark\Block\Element\IndentedCode;
use League\CommonMark\Block\Renderer\FencedCodeRenderer as CommonMarkCodeRenderer;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\Util\Xml;

class CodeRenderer extends CommonMarkCodeRenderer
{
    /**
     * {@inheritdoc}
     */
    public function render(AbstractBlock $block, ElementRendererInterface $htmlRenderer, $inTightList = false)
    {
        if (!($block instanceof FencedCode) && !($block instanceof IndentedCode)) {
            throw new \InvalidArgumentException('Incompatible block type: ' . get_class($block));
        }

        return Xml::escape($block->getStringContent());
    }
}
