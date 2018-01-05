<?php

namespace eLife\Annotations\Renderer\Block;

use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Element\BlockQuote;
use League\CommonMark\Block\Renderer\BlockQuoteRenderer as CommonMarkBlockQuoteRenderer;
use League\CommonMark\ElementRendererInterface;

class BlockQuoteRenderer extends CommonMarkBlockQuoteRenderer
{
    /**
     * @inheritdoc
     */
    public function render(AbstractBlock $block, ElementRendererInterface $htmlRenderer, $inTightList = false)
    {
        if (!($block instanceof BlockQuote)) {
            throw new \InvalidArgumentException('Incompatible block type: ' . get_class($block));
        }

        return $htmlRenderer->renderBlocks($block->children());
    }
}
