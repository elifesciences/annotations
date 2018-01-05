<?php

namespace eLife\Annotations\Renderer\Inline;

use eLife\Annotations\Renderer\Block\HtmlBlockRenderer;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\Inline\Element\AbstractInline;
use League\CommonMark\Inline\Renderer\HtmlInlineRenderer as CommonMarkHtmlInlineRenderer;

class ImageRenderer extends CommonMarkHtmlInlineRenderer
{
    /**
     * {@inheritdoc}
     */
    public function render(AbstractInline $inline, ElementRendererInterface $htmlRenderer)
    {
        return strip_tags(parent::render($inline, $htmlRenderer), HtmlBlockRenderer::ALLOWED_TAGS);
    }
}
