<?php

namespace eLife\Annotations\Serializer\CommonMark\Block\Renderer;

use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Renderer\BlockRendererInterface;
use League\CommonMark\ElementRendererInterface;
use function eLife\Annotations\Serializer\CommonMark\clean_paragraph;

class HtmlBlockRenderer implements BlockRendererInterface
{
    public function render(AbstractBlock $block, ElementRendererInterface $htmlRenderer, $inTightList = false)
    {
        return clean_paragraph($block->getStringContent());
    }
}
