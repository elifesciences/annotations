<?php

namespace tests\eLife\Annotations\Serializer\CommonMark;

use eLife\Annotations\Serializer\CommonMark\HtmlPurifierRenderer;
use HTMLPurifier;
use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\ElementRendererInterface;
use PHPUnit_Framework_TestCase;

/**
 * @covers \eLife\Annotations\Serializer\CommonMark\HtmlPurifierRenderer
 */
final class HtmlPurifierRendererTest extends PHPUnit_Framework_TestCase
{
    private $abstractBlock;
    /** @var HtmlPurifierRenderer */
    private $htmlPurifierRenderer;
    private $renderer;

    /**
     * @before
     */
    public function setup()
    {
        $this->renderer = $this->getMockBuilder(ElementRendererInterface::class)
            ->getMock();
        $this->abstractBlock = $this->createMock(AbstractBlock::class);
        $this->htmlPurifierRenderer = new HtmlPurifierRenderer($this->renderer, new HTMLPurifier(['Cache.SerializerPath' => sys_get_temp_dir()]));
    }

    /**
     * @test
     * @dataProvider purifyHtmlProvider
     */
    public function it_will_purify_html($expected, $rendered = null)
    {
        $this->renderer
            ->method('renderBlock')
            ->willReturn($rendered ?? $expected);
        $this->assertEquals($expected, $this->htmlPurifierRenderer->renderBlock($this->abstractBlock));
    }

    public function purifyHtmlProvider()
    {
        return [
            'clean' => [
                '<strong>Already</strong> clean html',
            ],
            'strip-tags' => [
                'iframe: ',
                'iframe: <iframe src="https://elifesciences.org"></iframe>',
            ],
            'strip-all-tags' => [
                '',
                '<iframe src="https://elifesciences.org"></iframe>',
            ],
            'script' => [
                '',
                '<script>evil()</script>',
            ],
            'anchor-onclick' => [
                '<a href="#">foobar</a>',
                '<a href="#" onclick="evil()">foobar</a>',
            ],
            'javascript' => [
                '<a>foobar</a>',
                '<a href="javascript:alert(\'evil\')">foobar</a>',
            ],
            'img-src-javascript' => [
                '',
                '<img src="javascript:alert(\'evil\')">',
            ],
        ];
    }
}
