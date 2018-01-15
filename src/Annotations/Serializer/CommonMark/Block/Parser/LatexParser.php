<?php

namespace eLife\Annotations\Serializer\CommonMark\Block\Parser;

use eLife\Annotations\Serializer\CommonMark\Block\Element\Latex;
use League\CommonMark\Block\Parser\AbstractBlockParser;
use League\CommonMark\ContextInterface;
use League\CommonMark\Cursor;

class LatexParser extends AbstractBlockParser
{
    public function parse(ContextInterface $context, Cursor $cursor)
    {
        $cursor->advanceToNextNonSpaceOrTab();
        $rest = $cursor->getRemainder();
        if (!preg_match('~^\$\$.+\$\$~', $rest)) {
            return false;
        }

        $context->addBlock(new Latex());

        return true;
    }
}
