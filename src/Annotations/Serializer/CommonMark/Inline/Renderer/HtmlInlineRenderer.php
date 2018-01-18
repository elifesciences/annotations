<?php

namespace eLife\Annotations\Serializer\CommonMark\Inline\Renderer;

use League\CommonMark\ElementRendererInterface;
use League\CommonMark\Inline\Element\AbstractInline;
use League\CommonMark\Inline\Renderer\InlineRendererInterface;
use function eLife\Annotations\Serializer\CommonMark\clean_paragraph;

class HtmlInlineRenderer implements InlineRendererInterface
{
    public function render(AbstractInline $inline, ElementRendererInterface $htmlRenderer)
    {
        return clean_paragraph($inline->getContent());
    }
}
