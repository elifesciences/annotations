<?php

namespace eLife\Annotations\Serializer\CommonMark;

use HTMLPurifier;
use League\CommonMark\Converter;
use League\HTMLToMarkdown\HtmlConverter;

class MarkdownSanitizer
{
    private $htmlConverter;
    private $htmlPurifier;
    private $markdownConverter;

    public function __construct(Converter $markdownConvertor, HtmlConverter $htmlConverter, HTMLPurifier $htmlPurifier)
    {
        $this->markdownConverter = $markdownConvertor;
        $this->htmlConverter = $htmlConverter;
        $this->htmlPurifier = $htmlPurifier;
    }

    public function parse(string $input) : string
    {
        $html = $this->markdownConverter->convertToHtml(encode_math($input));
        $purified = $this->htmlPurifier->purify($html);

        return decode_math($markdown = $this->htmlConverter->convert($purified));
    }
}
