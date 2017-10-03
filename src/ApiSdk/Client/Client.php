<?php

namespace eLife\HypothesisClient\ApiSdk\Client;

use eLife\HypothesisClient\ApiSdk\ArrayFromIterator;
use eLife\HypothesisClient\ApiSdk\Collection\Sequence;
use eLife\HypothesisClient\ApiSdk\SlicedArrayAccess;
use eLife\HypothesisClient\ApiSdk\SlicedIterator;

trait Client
{
    use ArrayFromIterator;
    use SlicedArrayAccess;
    use SlicedIterator {
        SlicedIterator::getPage insteadof SlicedArrayAccess;
        SlicedIterator::isEmpty insteadof SlicedArrayAccess;
        SlicedIterator::notEmpty insteadof SlicedArrayAccess;
        SlicedIterator::resetPages insteadof SlicedArrayAccess;
    }

    private $count;

    final public function __clone()
    {
        $this->resetIterator();
    }

    final public function count() : int
    {
        if (null === $this->count) {
            $this->slice(0, 1)->count();
        }

        return $this->count;
    }

    final public function flatten() : Sequence
    {
        return $this;
    }
}
