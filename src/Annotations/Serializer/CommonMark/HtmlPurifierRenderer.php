<?php

namespace eLife\Annotations\Serializer\CommonMark;

use HTMLPurifier;
use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\Environment;
use League\CommonMark\Inline\Element\AbstractInline;
use RuntimeException;

class HtmlPurifierRenderer implements ElementRendererInterface
{
    private $htmlPurifier;
    protected $environment;

    /**
     * @param Environment $environment
     */
    public function __construct(Environment $environment, HTMLPurifier $htmlPurifier)
    {
        $this->htmlPurifier = $htmlPurifier;
        $this->environment = $environment;
    }

    public function getOption($option, $default = null)
    {
        return $this->environment->getConfig('renderer/'.$option, $default);
    }

    private function renderInline(AbstractInline $inline) : string
    {
        $renderer = $this->environment->getInlineRendererForClass(get_class($inline));
        if (!$renderer) {
            throw new RuntimeException('Unable to find corresponding renderer for inline type '.get_class($inline));
        }

        return $renderer->render($inline, $this);
    }

    public function renderInlines($inlines) : string
    {
        $result = [];
        foreach ($inlines as $inline) {
            $result[] = $this->renderInline($inline);
        }

        return $this->htmlPurifier->purify($this->htmlPurifier->purify(implode('', $result)));
    }

    public function renderBlock(AbstractBlock $block, $inTightList = false) : string
    {
        $renderer = $this->environment->getBlockRendererForClass(get_class($block));
        if (!$renderer) {
            throw new RuntimeException('Unable to find corresponding renderer for block type '.get_class($block));
        }

        return $renderer->render($block, $this, $inTightList);
    }

    public function renderBlocks($blocks, $inTightList = false) : string
    {
        $result = [];
        foreach ($blocks as $block) {
            $result[] = $this->renderBlock($block, $inTightList);
        }

        $separator = $this->getOption('block_separator', "\n");

        return implode($separator, $result);
    }
}
