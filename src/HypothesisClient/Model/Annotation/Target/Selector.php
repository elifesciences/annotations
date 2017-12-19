<?php

namespace eLife\HypothesisClient\Model\Annotation\Target;

use eLife\HypothesisClient\Model\Annotation\Target\Selector\Fragment;
use eLife\HypothesisClient\Model\Annotation\Target\Selector\Range;
use eLife\HypothesisClient\Model\Annotation\Target\Selector\TextPosition;
use eLife\HypothesisClient\Model\Annotation\Target\Selector\TextQuote;

final class Selector
{
    private $range;
    private $textPosition;
    private $textQuote;
    private $fragment;

    /**
     * @internal
     */
    public function __construct(
        Range $range,
        TextPosition $textPosition,
        TextQuote $textQuote,
        Fragment $fragment = null
    ) {
        $this->range = $range;
        $this->textPosition = $textPosition;
        $this->textQuote = $textQuote;
        $this->fragment = $fragment;
    }

    public function getRange() : Range
    {
        return $this->range;
    }

    public function getTextPosition() : TextPosition
    {
        return $this->textPosition;
    }

    public function getTextQuote() : TextQuote
    {
        return $this->textQuote;
    }

    /**
     * @return Fragment|null
     */
    public function getFragment()
    {
        return $this->fragment;
    }
}
