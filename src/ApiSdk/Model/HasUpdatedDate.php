<?php

namespace eLife\HypothesisClient\ApiSdk\Model;

use DateTimeImmutable;

interface HasUpdatedDate
{
    /**
     * @return DateTimeImmutable|null
     */
    public function getUpdatedDate();
}
