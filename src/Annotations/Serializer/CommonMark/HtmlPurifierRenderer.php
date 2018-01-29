<?php

namespace eLife\Annotations\Serializer\CommonMark;

use HTMLPurifier;
use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\ElementRendererInterface;

class HtmlPurifierRenderer implements ElementRendererInterface
{
    const ALLOW_TAGS = '<i><strong><sub><sup><span><del><a><br><caption>';

    /** @var HTMLPurifier */
    private $htmlPurifier;
    /** @var ElementRendererInterface */
    private $renderer;

    public function __construct(ElementRendererInterface $renderer, HTMLPurifier $htmlPurifier)
    {
        $this->renderer = $renderer;
        $this->htmlPurifier = $htmlPurifier;
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
        $purified = $this->htmlPurifier->purify($rendered);

        return strip_tags($purified, self::ALLOW_TAGS);
    }

    public function renderBlocks($blocks, $inTightList = false) : string
    {
        return $this->renderer->renderBlocks($blocks, $inTightList);
    }
}
