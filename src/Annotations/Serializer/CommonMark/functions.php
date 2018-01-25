<?php

namespace eLife\Annotations\Serializer\CommonMark;

use League\CommonMark\Util\Xml;

function clean_paragraph(string $text) : string
{
    $allowed_tags = '<i><sub><sup><span><del><a><br><caption>';

    return strip_tags($text, $allowed_tags);
}

function escape_math(string $text) : string
{
    // Escape MathML.
    $escaped = preg_replace_callback('~<math[^>]*>.*?</math>~s', function ($match) {
        return Xml::escape($match[0]);
    }, $text);
    // Escape LaTeX.
    $escaped = preg_replace_callback('~\$\$.+\$\$~s', function ($match) {
        return Xml::escape($match[0]);
    }, $escaped);

    return $escaped;
}
