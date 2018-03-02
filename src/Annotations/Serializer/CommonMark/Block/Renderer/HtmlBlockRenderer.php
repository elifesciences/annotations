<?php

namespace eLife\Annotations\Serializer\CommonMark\Block\Renderer;

use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Renderer\BlockRendererInterface;
use League\CommonMark\ElementRendererInterface;

final class HtmlBlockRenderer implements BlockRendererInterface
{
    public function render(AbstractBlock $block, ElementRendererInterface $htmlRenderer, $inTightList = false)
    {
        return $block->getStringContent();
    }
}
