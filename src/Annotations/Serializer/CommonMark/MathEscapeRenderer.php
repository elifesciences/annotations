<?php

namespace eLife\Annotations\Serializer\CommonMark;

use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\Util\Xml;

class MathEscapeRenderer implements ElementRendererInterface
{
    private $renderer;

    public function __construct(ElementRendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    public function getOption($option, $default = null)
    {
        return $this->renderer->getOption($option, $default);
    }

    public function renderInlines($inlines) : string
    {
        return $this->renderer->renderInlines($inlines);
    }

    public function renderBlock(AbstractBlock $block, $inTightList = false) : string
    {
        $rendered = $this->renderer->renderBlock($block, $inTightList);
        // Escape MathML.
        $escaped = preg_replace_callback('~<math[^>]*>.*?</math>~s', function ($match) {
            return Xml::escape($match[0], true);
        }, $rendered);
        // Escape LaTeX.
        $escaped = preg_replace_callback('~\$\$.+\$\$~s', function ($match) {
            return Xml::escape($match[0], true);
        }, $escaped);

        return $escaped;
    }

    public function renderBlocks($blocks, $inTightList = false) : string
    {
        return $this->renderer->renderBlocks($blocks, $inTightList);
    }
}
