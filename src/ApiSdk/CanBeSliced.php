<?php

namespace eLife\HypothesisClient\ApiSdk;

use eLife\HypothesisClient\ApiSdk\Collection\Sequence;
use LogicException;

trait CanBeSliced
{
    use CanBeCounted;

    private $pages = [];
    private $pageBatch = 100;

    abstract public function slice(int $offset, int $length = null) : Sequence;

    final private function getPage(int $page) : Sequence
    {
        if (empty($this->pages)) {
            for ($i = 0; $i < $this->count(); ++$i) {
                if (0 === $i % $this->pageBatch) {
                    $this->pages[count($this->pages) + 1] = $this->slice($i, $this->pageBatch);
                }
            }
        }

        if (false === isset($this->pages[$page])) {
            throw new LogicException('Could not find page '.$page);
        }

        return $this->pages[$page];
    }

    final private function resetPages()
    {
        $this->pages = [];
        $this->pageBatch = 100;
    }
}
