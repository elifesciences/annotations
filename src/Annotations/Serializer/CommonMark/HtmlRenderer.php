<?php

namespace eLife\Annotations\Serializer\CommonMark;

use HTMLPurifier;
use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Environment;
use League\CommonMark\HtmlRenderer as CommonMarkHtmlRenderer;

class HtmlRenderer extends CommonMarkHtmlRenderer
{
    private $htmlPurifier;

    public function __construct(Environment $environment, HTMLPurifier $htmlPurifier)
    {
        $this->htmlPurifier = $htmlPurifier;
        parent::__construct($environment);
    }

    public function renderBlock(AbstractBlock $block, $inTightList = false) : string
    {
        return $this->htmlPurifier->purify(parent::renderBlock($block, $inTightList));
    }
}
