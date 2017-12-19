<?php

namespace eLife\HypothesisClient\Model\Annotation\Target\Selector;

final class TextQuote
{
    private $exact;
    private $prefix;
    private $suffix;

    /**
     * @internal
     */
    public function __construct(
        string $exact,
        string $prefix,
        string $suffix
    ) {
        $this->exact = $exact;
        $this->prefix = $prefix;
        $this->suffix = $suffix;
    }

    public function getExact() : string
    {
        return $this->exact;
    }

    public function getPrefix() : string
    {
        return $this->prefix;
    }

    public function getSuffix() : string
    {
        return $this->suffix;
    }
}
