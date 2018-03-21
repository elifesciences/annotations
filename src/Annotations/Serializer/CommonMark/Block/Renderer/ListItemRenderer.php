<?php

namespace eLife\Annotations\Serializer\CommonMark\Block\Renderer;

use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Element\ListItem;
use League\CommonMark\Block\Renderer\BlockRendererInterface;
use League\CommonMark\ElementRendererInterface;

final class ListItemRenderer implements BlockRendererInterface
{
    public function render(AbstractBlock $block, ElementRendererInterface $htmlRenderer, $inTightList = false)
    {
        if (!($block instanceof ListItem)) {
            throw new \InvalidArgumentException('Incompatible block type: '.get_class($block));
        }

        return $htmlRenderer->renderBlocks($block->children(), $inTightList);
    }
}
