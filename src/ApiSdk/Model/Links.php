<?php

namespace eLife\HypothesisClient\ApiSdk\Model;

final class Links implements CastsToString
{
    private $incontext;
    private $json;
    private $html;

    public function __construct(
        string $incontext = null,
        string $json = null,
        string $html = null
    ) {
        $this->incontext = $incontext;
        $this->json = $json;
        $this->html = $html;
    }

    /**
     * @return string
     */
    public function getIncontext() : string
    {
        return $this->incontext;
    }

    /**
     * @return string|null
     */
    public function getJson()
    {
        return $this->json;
    }

    /**
     * @return string|null
     */
    public function getHtml()
    {
        return $this->html;
    }

    public function toString() : string
    {
        return $this->getIncontext();
    }
}
