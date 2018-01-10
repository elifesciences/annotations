<?php

namespace eLife\Annotations\Serializer\CommonMark;

trait FilterTags
{
    private $allowed_tags = '<i><sub><sup><span><del><math><a><br><table><caption>';

    private function filter_tags($html)
    {
        $tmp = strip_tags($html, $this->allowed_tags);

        return $tmp;
    }
}
