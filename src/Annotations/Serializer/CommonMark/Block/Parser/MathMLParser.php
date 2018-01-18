<?php

namespace eLife\Annotations\Serializer\CommonMark\Block\Parser;

use eLife\Annotations\Serializer\CommonMark\Block\Element\MathML;
use League\CommonMark\Block\Parser\AbstractBlockParser;
use League\CommonMark\ContextInterface;
use League\CommonMark\Cursor;

class MathMLParser extends AbstractBlockParser
{
    public function parse(ContextInterface $context, Cursor $cursor)
    {
        $cursor->advanceToNextNonSpaceOrTab();
        $rest = $cursor->getRemainder();
        if (!preg_match('~^<math~', $rest)) {
            return false;
        }

        $context->addBlock(new MathML());

        return true;
    }
}
