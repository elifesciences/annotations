<?php

namespace tests\eLife\Annotations\Serializer\CommonMark;

use eLife\Annotations\Serializer\CommonMark\MarkdownSanitizer;
use tests\eLife\Annotations\WebTestCase;

/**
 * @covers \eLife\Annotations\Serializer\CommonMark\MarkdownSanitizer
 */
final class MarkdownSanitizerWebTest extends WebTestCase
{
    /** @var MarkdownSanitizer */
    private $sanitizer;

    /**
     * @before
     */
    protected function setUpSanitizer()
    {
        $this->setUpApp();
        $this->sanitizer = $this->getApp()->get('annotation.serializer.common_mark.markdown_sanitizer');
    }

    /**
     * @test
     * @dataProvider canSanitizeProvider
     */
    public function it_can_sanitize(string $markdown, string $expected = null)
    {
        $this->assertSame($expected ?? $markdown, $this->sanitizer->parse($markdown));
    }

    public function canSanitizeProvider() : array
    {
        return [
            'onclick' => [
                '<a href="#" onclick="evil()">foobar</a> <a href="#" onclick=evil()>foobar</a>',
                '[foobar](#) [foobar](#)',
            ],
            'no-href' => [
                '<a onclick="evil()">foobar</a>',
                '<a>foobar</a>',
            ],
            'javascript' => [
                '<a href="javascript:alert(\'evil\')">foobar</a>',
                '<a>foobar</a>',
            ],
            'mathml' => [
                '<math xmlns="http://www.w3.org/1998/Math/MathML"><mstyle mathcolor="blue" fontfamily="serif" displaystyle="true"><mi>a</mi><msup><mi>x</mi><mn>2</mn></msup><mo>+</mo><mi>b</mi><mi>x</mi><mo>+</mo><mi>c</mi><mo>=</mo><mn>0</mn></mstyle></math>',
                '&lt;math xmlns=&quot;http://www.w3.org/1998/Math/MathML&quot;&gt;&lt;mstyle mathcolor=&quot;blue&quot; fontfamily=&quot;serif&quot; displaystyle=&quot;true&quot;&gt;&lt;mi&gt;a&lt;/mi&gt;&lt;msup&gt;&lt;mi&gt;x&lt;/mi&gt;&lt;mn&gt;2&lt;/mn&gt;&lt;/msup&gt;&lt;mo&gt;+&lt;/mo&gt;&lt;mi&gt;b&lt;/mi&gt;&lt;mi&gt;x&lt;/mi&gt;&lt;mo&gt;+&lt;/mo&gt;&lt;mi&gt;c&lt;/mi&gt;&lt;mo&gt;=&lt;/mo&gt;&lt;mn&gt;0&lt;/mn&gt;&lt;/mstyle&gt;&lt;/math&gt;',
            ],
            'latex' => [
                $this->lines([
                    'Math inline \\(k_{n+1} = n^2 + k_n^2 - k_{n-1}\\)'.PHP_EOL,
                    'And a block of math for larger equations:'.PHP_EOL,
                    '$$',
                    '\\forall x \\in X,',
                    '\\quad \\exists y',
                    '\\leq \\epsilon',
                    '$$',
                ]),
                $this->lines([
                    'Math inline (k\_{n+1} = n^2 + k\_n^2 - k\_{n-1})'.PHP_EOL,
                    'And a block of math for larger equations:'.PHP_EOL,
                    '$$',
                    '\\forall x \\in X,',
                    '\\quad \\exists y',
                    '\\leq \\epsilon',
                    '$$',
                ]),
            ],
            'code' => [
                "To check I understand the requirements here, you want to be able to index a conversation thread (annotation + all replies) as one ES document, and then in response to a query, return a data structure which contains the IDs of matching conversations plus the IDs of matching items (annotation or original reply) within those conversations?\n\nSo this is essentially the same problem as say, finding out which page matched if you were indexing multi-page documents?\n\nPresumably ES can store position information with indexed terms. In that case here is one possible approach: Take all of the original items in the thread and serialize them into a single string - which is indexed with positional information, and separately the offsets of each item within that string are stored as a non-indexed field.\n\neg:\n\n```\n\"content\" field: annotation content | first reply | second reply\n\"offsets\" field: <first reply ID>:<offset of first reply>,<second reply ID>:<offset of second reply>\n```\n\nWhen a search query is received, an ES query is performed to find the matching documents and get the offsets of matches within the \"content\" field. These offsets are then looked up in the \"offsets\" field to get the thread IDs.",
                "To check I understand the requirements here, you want to be able to index a conversation thread (annotation + all replies) as one ES document, and then in response to a query, return a data structure which contains the IDs of matching conversations plus the IDs of matching items (annotation or original reply) within those conversations?\n\nSo this is essentially the same problem as say, finding out which page matched if you were indexing multi-page documents?\n\nPresumably ES can store position information with indexed terms. In that case here is one possible approach: Take all of the original items in the thread and serialize them into a single string - which is indexed with positional information, and separately the offsets of each item within that string are stored as a non-indexed field.\n\neg:\n\n```\n\"content\" field: annotation content | first reply | second reply\n\"offsets\" field: <first reply ID>:<offset of first reply>,<second reply ID>:<offset of second reply>\n\n```\n\nWhen a search query is received, an ES query is performed to find the matching documents and get the offsets of matches within the \"content\" field. These offsets are then looked up in the \"offsets\" field to get the thread IDs.",
            ],
            'italics' => [
                "**Editors' choice**\n\nHow do soluble secretory proteins leave the endoplasmic reticulum in animal cells – by nonselective bulk flow or by receptor-mediated export? Chen et al. shed light on this question in a study of knockout mice that lack Sec24A, which is a component of COPII-coated vesicles. These mutant mice manifest low plasma cholesterol levels because the secretion of PCSK9 - a liver-derived plasma protein that destroys low-density lipoprotein (LDL) receptors and elevates LDL-cholesterol – is blocked. These results suggest the existence of a putative membrane receptor that recruits PCSK9 to COPII vesicles for export.\n\nJoe Goldstein\n\nSenior Editor, *eLife*\n\nMike Brown\n\nBoard of Reviewing Editors, *eLife*\n\nSee also the related Insight article by DeBose-Boyd and Horton: ",
                "**Editors' choice**\n\nHow do soluble secretory proteins leave the endoplasmic reticulum in animal cells – by nonselective bulk flow or by receptor-mediated export? Chen et al. shed light on this question in a study of knockout mice that lack Sec24A, which is a component of COPII-coated vesicles. These mutant mice manifest low plasma cholesterol levels because the secretion of PCSK9 - a liver-derived plasma protein that destroys low-density lipoprotein (LDL) receptors and elevates LDL-cholesterol – is blocked. These results suggest the existence of a putative membrane receptor that recruits PCSK9 to COPII vesicles for export.\n\nJoe Goldstein\n\nSenior Editor, *eLife*\n\nMike Brown\n\nBoard of Reviewing Editors, *eLife*\n\nSee also the related Insight article by DeBose-Boyd and Horton:",
            ],
        ];
    }

    private function lines(array $lines, $delimiter = PHP_EOL)
    {
        return implode($delimiter, $lines);
    }
}
