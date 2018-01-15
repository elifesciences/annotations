<?php

namespace eLife\Annotations\Serializer\CommonMark\Block\Renderer;

use eLife\Annotations\Serializer\CommonMark\FilterTags;
use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Renderer\HtmlBlockRenderer as CommonMarkHtmlBlockRenderer;
use League\CommonMark\ElementRendererInterface;

class HtmlBlockRenderer extends CommonMarkHtmlBlockRenderer
{
    use FilterTags;

    public function render(AbstractBlock $block, ElementRendererInterface $htmlRenderer, $inTightList = false)
    {
        return $this->filter_tags(parent::render($block, $htmlRenderer, $inTightList));
    }
}
