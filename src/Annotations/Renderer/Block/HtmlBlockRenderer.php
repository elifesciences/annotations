<?php

namespace eLife\Annotations\Renderer\Block;

use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Renderer\HtmlBlockRenderer as CommonMarkHtmlBlockRenderer;
use League\CommonMark\ElementRendererInterface;

class HtmlBlockRenderer extends CommonMarkHtmlBlockRenderer
{
    const ALLOWED_TAGS = '<i><sub><sup><span><del><math><a><br><table><caption>';

    /**
     * {@inheritdoc}
     */
    public function render(AbstractBlock $block, ElementRendererInterface $htmlRenderer, $inTightList = false)
    {
        return strip_tags(parent::render($block, $htmlRenderer, $inTightList), self::ALLOWED_TAGS);
    }
}
