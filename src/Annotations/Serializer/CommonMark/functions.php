<?php

namespace eLife\Annotations\Serializer\CommonMark;

use League\CommonMark\Util\Xml;

function clean_paragraph($text)
{
    $allowed_tags = '<i><sub><sup><span><del><math><a><br><caption>';

    return strip_tags($text, $allowed_tags);
}

function escape_math($text)
{
    // Escape MathML.
    $escaped = preg_replace_callback('~(?P<before><math[^>]*>)(?P<mathml>.*)(?P<after></math>)~s', function ($match) {
        return Xml::escape($match['before'].$match['mathml'].$match['after']);
    }, $text);
    // Escape LaTeX.
    $escaped = preg_replace_callback('~(?P<before>\$\$)(?P<latex>.+)(?P<after>\$\$)~s', function ($match) {
        return Xml::escape($match['before'].$match['latex'].$match['after']);
    }, $escaped);
    return $escaped;
}
