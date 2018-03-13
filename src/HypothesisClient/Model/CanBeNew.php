<?php

namespace eLife\HypothesisClient\Model;

trait CanBeNew
{
    protected $new = false;

    public function isNew() : bool
    {
        return $this->new;
    }
}
