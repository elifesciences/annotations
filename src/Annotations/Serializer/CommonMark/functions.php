<?php

namespace eLife\Annotations\Serializer\CommonMark;

use League\CommonMark\Util\Xml;

function clean_paragraph($text)
{
    $allowed_tags = '<i><sub><sup><span><del><math><a><br><caption>';

    return strip_tags($text, $allowed_tags);
}

function encode_mathml($text)
{
    return preg_replace_callback('~(?P<before><math[^>]*>)(?P<mathml>.*)(?P<after></math>)~s', function ($match) {
        return encode_string($match['before'].$match['mathml'].$match['after'], true, 'MATH');
    }, $text);
}

function decode_mathml($text)
{
    return decode_string($text, 'MATH');
}

function encode_latex($text)
{
    return preg_replace_callback('~(?P<before>\$\$)(?P<latex>.+)(?P<after>\$\$)~s', function ($match) {
        return encode_string($match['before'].$match['latex'].$match['after'], true, 'LATEX');
    }, $text);
}

function decode_latex($text)
{
    return decode_string($text, 'LATEX');
}

function encode_math($text)
{
    return encode_latex(encode_mathml($text));
}

function decode_math($text)
{
    return decode_latex(decode_mathml($text));
}

function encode_string($text, $escape = false, $prefix = '')
{
    return '|'.$prefix.'ENCODESTART|'.base64_encode($escape ? Xml::escape($text) : $text).'|'.$prefix.'ENCODEEND|';
}

function decode_string($text, $prefix = '')
{
    $preg_prefix = preg_quote($prefix, '~');

    return preg_replace_callback('~(?P<before>\|'.$preg_prefix.'ENCODESTART\|)(?P<string>.+)(?P<after>\|'.$preg_prefix.'ENCODEEND\|)~s', function ($match) {
        return base64_decode($match['string']);
    }, $text);
}
