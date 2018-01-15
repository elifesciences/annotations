<?php

namespace eLife\Annotations\Serializer\CommonMark;

function clean_paragraph($text)
{
    $allowed_tags = '<i><sub><sup><span><del><math><a><br><caption>';
    return strip_tags($text, $allowed_tags);
}
