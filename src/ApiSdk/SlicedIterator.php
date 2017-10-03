<?php

namespace eLife\HypothesisClient\ApiSdk;

use OutOfRangeException;

trait SlicedIterator
{
    use CanBeSliced;

    private $key = 1;

    final public function current()
    {
        $page = (int) ceil($this->key / $this->pageBatch);
        $inPage = $this->key - ($page * $this->pageBatch) + $this->pageBatch - 1;

        $pageContents = $this->getPage($page);
        if (!isset($pageContents[$inPage])) {
            throw new OutOfRangeException("Cannot find element with key $inPage in page $page");
        }

        return $pageContents[$inPage];
    }

    final public function next()
    {
        ++$this->key;
    }

    final public function key()
    {
        if ($this->key > $this->count()) {
            return null;
        }

        return $this->key;
    }

    final public function valid()
    {
        return $this->key <= $this->count();
    }

    final public function rewind()
    {
        $this->key = 1;
    }

    final private function resetIterator()
    {
        $this->rewind();
        $this->resetPages();
    }
}
