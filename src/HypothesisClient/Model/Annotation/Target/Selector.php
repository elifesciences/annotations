<?php

namespace eLife\HypothesisClient\Model\Annotation\Target;

use eLife\HypothesisClient\Model\Annotation\Target\Selector\TextQuote;

final class Selector
{
    private $textQuote;

    /**
     * @internal
     */
    public function __construct(
        TextQuote $textQuote
    ) {
        $this->textQuote = $textQuote;
    }

    public function getTextQuote() : TextQuote
    {
        return $this->textQuote;
    }
}
