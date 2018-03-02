<?php

namespace tests\eLife\Annotations\Serializer\CommonMark;

use eLife\Annotations\Serializer\CommonMark\MathEscapeRenderer;
use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\ElementRendererInterface;
use PHPUnit_Framework_TestCase;

/**
 * @covers \eLife\Annotations\Serializer\CommonMark\MathEscapeRenderer
 */
final class MathEscapeRendererTest extends PHPUnit_Framework_TestCase
{
    private $abstractBlock;
    /** @var MathEscapeRenderer */
    private $mathEscapeRenderer;
    private $renderer;

    /**
     * @before
     */
    public function setup()
    {
        $this->renderer = $this->getMockBuilder(ElementRendererInterface::class)
            ->getMock();
        $this->abstractBlock = $this->createMock(AbstractBlock::class);
        $this->mathEscapeRenderer = new MathEscapeRenderer($this->renderer);
    }

    /**
     * @test
     * @dataProvider mathMlAndLatexProvider
     */
    public function it_will_escape_mathml_and_latex($expected, $rendered = null)
    {
        $this->renderer
            ->method('renderBlock')
            ->willReturn($rendered ?? $expected);
        $this->assertEquals($expected, $this->mathEscapeRenderer->renderBlock($this->abstractBlock));
    }

    public function mathMlAndLatexProvider()
    {
        return [
            'no-math' => [
                'No math at all',
            ],
            'mathml' => [
                '&lt;math xmlns=&quot;http://www.w3.org/1998/Math/MathML&quot;&gt;&lt;mstyle mathcolor=&quot;blue&quot; fontfamily=&quot;serif&quot; displaystyle=&quot;true&quot;&gt;&lt;mi&gt;a&lt;/mi&gt;&lt;msup&gt;&lt;mi&gt;x&lt;/mi&gt;&lt;mn&gt;2&lt;/mn&gt;&lt;/msup&gt;&lt;mo&gt;+&lt;/mo&gt;&lt;mi&gt;b&lt;/mi&gt;&lt;mi&gt;x&lt;/mi&gt;&lt;mo&gt;+&lt;/mo&gt;&lt;mi&gt;c&lt;/mi&gt;&lt;mo&gt;=&lt;/mo&gt;&lt;mn&gt;0&lt;/mn&gt;&lt;/mstyle&gt;&lt;/math&gt;',
                '<math xmlns="http://www.w3.org/1998/Math/MathML"><mstyle mathcolor="blue" fontfamily="serif" displaystyle="true"><mi>a</mi><msup><mi>x</mi><mn>2</mn></msup><mo>+</mo><mi>b</mi><mi>x</mi><mo>+</mo><mi>c</mi><mo>=</mo><mn>0</mn></mstyle></math>',
            ],
            'mathml-mulitple' => [
                '&lt;math xmlns=&quot;http://www.w3.org/1998/Math/MathML&quot;&gt;&lt;mi&gt;a&lt;/mi&gt;&lt;/math&gt; <a href="https://elifesciences.org">some other text</a> &lt;math xmlns=&quot;http://www.w3.org/1998/Math/MathML&quot;&gt;&lt;mi&gt;a&lt;/mi&gt;&lt;/math&gt; ',
                '<math xmlns="http://www.w3.org/1998/Math/MathML"><mi>a</mi></math> <a href="https://elifesciences.org">some other text</a> <math xmlns="http://www.w3.org/1998/Math/MathML"><mi>a</mi></math> ',
            ],
            'latex' => [
                $this->lines([
                    '$$',
                    '\\forall x \\in X,',
                    '\\quad \\exists y',
                    '\\leq&lt; \\epsilon',
                    '$$',
                ]),
                $this->lines([
                    '$$',
                    '\\forall x \\in X,',
                    '\\quad \\exists y',
                    '\\leq< \\epsilon',
                    '$$',
                ]),
            ],
        ];
    }

    private function lines(array $lines)
    {
        return implode(PHP_EOL, $lines);
    }
}
