<?php

namespace eLife\Annotations\Serializer\CommonMark\Inline\Renderer;

use eLife\Annotations\Serializer\CommonMark\FilterTags;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\Inline\Element\AbstractInline;
use League\CommonMark\Inline\Renderer\HtmlInlineRenderer as CommonMarkHtmlInlineRenderer;

class HtmlInlineRenderer extends CommonMarkHtmlInlineRenderer
{
    use FilterTags;

    /**
     * {@inheritdoc}
     */
    public function render(AbstractInline $inline, ElementRendererInterface $htmlRenderer)
    {
        return $this->filter_tags(parent::render($inline, $htmlRenderer));
    }
}
