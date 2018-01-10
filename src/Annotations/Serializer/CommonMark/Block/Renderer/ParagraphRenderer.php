<?php

namespace eLife\Annotations\Serializer\CommonMark\Block\Renderer;

use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Element\Paragraph;
use League\CommonMark\Block\Renderer\ParagraphRenderer as CommonMarkParagraphRenderer;
use League\CommonMark\ElementRendererInterface;

class ParagraphRenderer extends CommonMarkParagraphRenderer
{
    /**
     * {@inheritdoc}
     */
    public function render(AbstractBlock $block, ElementRendererInterface $htmlRenderer, $inTightList = false)
    {
        if (!($block instanceof Paragraph)) {
            throw new \InvalidArgumentException('Incompatible block type: '.get_class($block));
        }

        return $htmlRenderer->renderInlines($block->children());
    }
}
