<?php

namespace eLife\HypothesisClient\Model\Annotation\Target;

use eLife\HypothesisClient\Model\Annotation\Target\Selector\Fragment;
use eLife\HypothesisClient\Model\Annotation\Target\Selector\Range;
use eLife\HypothesisClient\Model\Annotation\Target\Selector\TextPosition;
use eLife\HypothesisClient\Model\Annotation\Target\Selector\TextQuote;

final class Selector
{
    private $fragment;
    private $range;
    private $textPosition;
    private $textQuote;

    /**
     * @internal
     */
    public function __construct(
        TextQuote $textQuote,
        TextPosition $textPosition = null,
        Range $range = null,
        Fragment $fragment = null
    ) {
        $this->textPosition = $textPosition;
        $this->textQuote = $textQuote;
        $this->range = $range;
        $this->fragment = $fragment;
    }

    public function getTextQuote() : TextQuote
    {
        return $this->textQuote;
    }

    /**
     * @return TextPosition|null
     */
    public function getTextPosition()
    {
        return $this->textPosition;
    }

    /**
     * @return Range|null
     */
    public function getRange()
    {
        return $this->range;
    }

    /**
     * @return Fragment|null
     */
    public function getFragment()
    {
        return $this->fragment;
    }
}
