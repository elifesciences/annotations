<?php

namespace eLife\Annotations\Serializer\CommonMark;

use League\CommonMark\DocParser as CommonMarkDocParser;

class DocParser extends CommonMarkDocParser
{
    public function parse($input)
    {
        // Encode LaTeX.
        $input = preg_replace_callback('~(?P<before>\$\$)(?P<latex>.+)(?P<after>\$\$)~s', function ($match) {
            return $match['before'].base64_encode($match['latex']).$match['after'];
        }, $input);
        // Encode MathML.
        $input = preg_replace_callback('~(?P<before><math[^>]*>)(?P<mathml>.*)(?P<after></math>)~s', function ($match) {
            return $match['before'].base64_encode($match['mathml']).$match['after'];
        }, $input);

        return parent::parse($input);
    }
}
