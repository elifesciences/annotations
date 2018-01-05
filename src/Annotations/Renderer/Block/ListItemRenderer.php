<?php

namespace eLife\Annotations\Renderer\Block;

use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Element\ListItem;
use League\CommonMark\Block\Renderer\ListItemRenderer as CommonMarkListItemRenderer;
use League\CommonMark\ElementRendererInterface;

class ListItemRenderer extends CommonMarkListItemRenderer
{
    /**
     * @inherit
     */
    public function render(AbstractBlock $block, ElementRendererInterface $htmlRenderer, $inTightList = false)
    {
        if (!($block instanceof ListItem)) {
            throw new \InvalidArgumentException('Incompatible block type: '.get_class($block));
        }

        return $htmlRenderer->renderBlocks($block->children(), $inTightList);
    }
}
