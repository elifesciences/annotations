<?php

namespace tests\eLife\Annotations\Serializer;

use DateTimeImmutable;
use DateTimeZone;
use eLife\Annotations\Serializer\CommonMark;
use eLife\Annotations\Serializer\HypothesisClientAnnotationNormalizer;
use eLife\ApiSdk\Serializer\Block;
use eLife\ApiSdk\Serializer\NormalizerAwareSerializer;
use eLife\HypothesisClient\Model\Annotation;
use League\CommonMark\Block as CommonMarkBlock;
use League\CommonMark\DocParser;
use League\CommonMark\Environment;
use League\CommonMark\HtmlRenderer;
use League\CommonMark\Inline as CommonMarkInline;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Debug\BufferingLogger;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @covers \eLife\Annotations\Serializer\HypothesisClientAnnotationNormalizer
 */
final class HypothesisClientAnnotationNormalizerTest extends PHPUnit_Framework_TestCase
{
    /** @var DocParser */
    private $docParser;
    /** @var HtmlRenderer */
    private $htmlRenderer;
    /** @var BufferingLogger */
    private $logger;
    /** @var HypothesisClientAnnotationNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $environment = Environment::createCommonMarkEnvironment();

        $environment->addBlockParser(new CommonMark\Block\Parser\LatexParser());
        $environment->addBlockParser(new CommonMark\Block\Parser\MathMLParser());

        $environment->addBlockRenderer(CommonMark\Block\Element\Latex::class, new CommonMark\Block\Renderer\LatexRenderer());
        $environment->addBlockRenderer(CommonMark\Block\Element\MathML::class, new CommonMark\Block\Renderer\MathMLRenderer());
        $environment->addBlockRenderer(CommonMarkBlock\Element\BlockQuote::class, new CommonMark\Block\Renderer\BlockQuoteRenderer());
        $environment->addBlockRenderer(CommonMarkBlock\Element\FencedCode::class, new CommonMark\Block\Renderer\CodeRenderer());
        $environment->addBlockRenderer(CommonMarkBlock\Element\HtmlBlock::class, new CommonMark\Block\Renderer\HtmlBlockRenderer());
        $environment->addBlockRenderer(CommonMarkBlock\Element\IndentedCode::class, new CommonMark\Block\Renderer\CodeRenderer());
        $environment->addBlockRenderer(CommonMarkBlock\Element\ListItem::class, new CommonMark\Block\Renderer\ListItemRenderer());
        $environment->addBlockRenderer(CommonMarkBlock\Element\Paragraph::class, new CommonMark\Block\Renderer\ParagraphRenderer());

        $environment->addInlineRenderer(CommonMarkInline\Element\HtmlInline::class, new CommonMark\Inline\Renderer\HtmlInlineRenderer());
        $environment->addInlineRenderer(CommonMarkInline\Element\Image::class, new CommonMark\Inline\Renderer\ImageRenderer());

        $this->docParser = new CommonMark\DocParser($environment);
        $this->htmlRenderer = new HtmlRenderer($environment);

        $this->logger = new BufferingLogger();
        // @todo - I'm not sure why Symfony\Component\Serializer\Serializer doesn't work here.
        $this->normalizer = new NormalizerAwareSerializer([
            new HypothesisClientAnnotationNormalizer($this->docParser, $this->htmlRenderer, $this->logger),
            new Block\CodeNormalizer(),
            new Block\ListingNormalizer(),
            new Block\MathMLNormalizer(),
            new Block\ParagraphNormalizer(),
            new Block\QuoteNormalizer(),
            new Block\YouTubeNormalizer(),
        ]);
    }

    /**
     * @test
     */
    public function it_is_a_normalizer()
    {
        $this->assertInstanceOf(NormalizerInterface::class, $this->normalizer);
    }

    /**
     * @test
     * @dataProvider canNormalizeProvider
     */
    public function it_can_normalize_annotations($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $annotation = new Annotation(
            'id',
            'text',
            new DateTimeImmutable('now', new DateTimeZone('Z')),
            new DateTimeImmutable('now', new DateTimeZone('Z')),
            new Annotation\Document('title'),
            new Annotation\Target('source'),
            'uri',
            null,
            new Annotation\Permissions('read')
        );

        return [
            'annotation' => [$annotation, null, true],
            'non-annotation' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_will_normalize_annotations(array $expected, Annotation $annotation)
    {
        $this->assertEquals($expected, $this->normalizer->normalize($annotation));
    }

    public function normalizeProvider() : array
    {
        $createdDate = '2017-11-29T17:41:28Z';
        $updatedDate = '2018-01-04T11:23:47Z';

        return [
            'complete' => [
                [
                    'id' => 'id',
                    'access' => 'public',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'text',
                        ],
                    ],
                    'highlight' => 'highlight',
                    'created' => $createdDate,
                    'updated' => $updatedDate,
                    'document' => [
                        'title' => 'title',
                        'uri' => 'uri',
                    ],
                    'parents' => [
                        'parent1',
                        'parent2',
                    ],
                ],
                new Annotation(
                    'id',
                    'text',
                    new DateTimeImmutable($createdDate),
                    new DateTimeImmutable($updatedDate),
                    new Annotation\Document('title'),
                    new Annotation\Target(
                        'source',
                        new Annotation\Target\Selector(
                            new Annotation\Target\Selector\TextQuote('highlight', 'prefix', 'suffix')
                        )
                    ),
                    'uri',
                    [
                        'parent1',
                        'parent2',
                    ],
                    new Annotation\Permissions(Annotation::PUBLIC_GROUP)
                ),
            ],
            'minimum' => [
                [
                    'id' => 'id',
                    'access' => 'public',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'text',
                        ],
                    ],
                    'created' => $createdDate,
                    'document' => [
                        'title' => 'title',
                        'uri' => 'uri',
                    ],
                    'parents' => [],
                ],
                new Annotation(
                    'id',
                    'text',
                    new DateTimeImmutable($createdDate),
                    new DateTimeImmutable($createdDate),
                    new Annotation\Document('title'),
                    new Annotation\Target('source'),
                    'uri',
                    null,
                    new Annotation\Permissions(Annotation::PUBLIC_GROUP)
                ),
            ],
            'no-content-or-higlight' => [
                [
                    'id' => 'id',
                    'access' => 'public',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'NOTE: There is no content available to display.',
                        ],
                    ],
                    'created' => $createdDate,
                    'document' => [
                        'title' => 'title',
                        'uri' => 'uri',
                    ],
                    'parents' => [],
                ],
                new Annotation(
                    'id',
                    null,
                    new DateTimeImmutable($createdDate),
                    new DateTimeImmutable($createdDate),
                    new Annotation\Document('title'),
                    new Annotation\Target('source'),
                    'uri',
                    null,
                    new Annotation\Permissions(Annotation::PUBLIC_GROUP)
                ),
            ],
            'markdown-multiple-paragraphs' => [
                [
                    'id' => 'id',
                    'access' => 'public',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'paragraph 1, <strong>with bold text</strong>',
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => 'paragraph 2',
                        ],
                    ],
                    'created' => $createdDate,
                    'document' => [
                        'title' => 'title',
                        'uri' => 'uri',
                    ],
                    'parents' => [],
                ],
                new Annotation(
                    'id',
                    $this->lines([
                        '   paragraph 1, **with bold text**'.PHP_EOL,
                        'paragraph 2',
                    ]),
                    new DateTimeImmutable($createdDate),
                    new DateTimeImmutable($createdDate),
                    new Annotation\Document('title'),
                    new Annotation\Target('source'),
                    'uri',
                    null,
                    new Annotation\Permissions(Annotation::PUBLIC_GROUP)
                ),
            ],
            'markdown-lists' => [
                [
                    'id' => 'id',
                    'access' => 'public',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'List:',
                        ],
                        [
                            'type' => 'list',
                            'prefix' => 'bullet',
                            'items' => [
                                'Item 1',
                                'Item 2',
                            ],
                        ],
                        [
                            'type' => 'list',
                            'prefix' => 'number',
                            'items' => [
                                'Item 1',
                                'Item 2',
                                'Item 3',
                            ],
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => 'Final paragraph.',
                        ],
                    ],
                    'created' => $createdDate,
                    'document' => [
                        'title' => 'title',
                        'uri' => 'uri',
                    ],
                    'parents' => [],
                ],
                new Annotation(
                    'id',
                    $this->lines([
                        'List:'.PHP_EOL,
                        '- Item 1',
                        '- Item 2  '.PHP_EOL,
                        '1. Item 1',
                        '1. Item 2',
                        '1. Item 3'.PHP_EOL,
                        'Final paragraph.',
                    ]),
                    new DateTimeImmutable($createdDate),
                    new DateTimeImmutable($createdDate),
                    new Annotation\Document('title'),
                    new Annotation\Target('source'),
                    'uri',
                    null,
                    new Annotation\Permissions(Annotation::PUBLIC_GROUP)
                ),
            ],
            'markdown-nested-lists' => [
                [
                    'id' => 'id',
                    'access' => 'public',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'Nested list:',
                        ],
                        [
                            'type' => 'list',
                            'prefix' => 'bullet',
                            'items' => [
                                'Item 1',
                                'Item 2',
                                [
                                    [
                                        'type' => 'list',
                                        'prefix' => 'bullet',
                                        'items' => [
                                            'Item 2.1',
                                            [
                                                [
                                                    'type' => 'list',
                                                    'prefix' => 'number',
                                                    'items' => [
                                                        'Item 2.1.1',
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'created' => $createdDate,
                    'document' => [
                        'title' => 'title',
                        'uri' => 'uri',
                    ],
                    'parents' => [],
                ],
                new Annotation(
                    'id',
                    $this->lines([
                        'Nested list:'.PHP_EOL,
                        '- Item 1',
                        '- Item 2',
                        '  - Item 2.1',
                        '    1. Item 2.1.1',
                    ]),
                    new DateTimeImmutable($createdDate),
                    new DateTimeImmutable($createdDate),
                    new Annotation\Document('title'),
                    new Annotation\Target('source'),
                    'uri',
                    null,
                    new Annotation\Permissions(Annotation::PUBLIC_GROUP)
                ),
            ],
            'markdown-quotes' => [
                [
                    'id' => 'id',
                    'access' => 'public',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'Opening paragraph',
                        ],
                        [
                            'type' => 'quote',
                            'text' => [
                                [
                                    'type' => 'paragraph',
                                    'text' => 'Quote',
                                ],
                            ],
                        ],
                        [
                            'type' => 'quote',
                            'text' => [
                                [
                                    'type' => 'paragraph',
                                    'text' => 'Another quote',
                                ],
                            ],
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => 'Closing paragraph',
                        ],
                    ],
                    'created' => $createdDate,
                    'document' => [
                        'title' => 'title',
                        'uri' => 'uri',
                    ],
                    'parents' => [],
                ],
                new Annotation(
                    'id',
                    $this->lines([
                        'Opening paragraph'.PHP_EOL,
                        '> Quote'.PHP_EOL,
                        '> Another quote'.PHP_EOL,
                        'Closing paragraph',
                    ]),
                    new DateTimeImmutable($createdDate),
                    new DateTimeImmutable($createdDate),
                    new Annotation\Document('title'),
                    new Annotation\Target('source'),
                    'uri',
                    null,
                    new Annotation\Permissions(Annotation::PUBLIC_GROUP)
                ),
            ],
            'markdown-complex-1' => [
                [
                    'id' => 'id',
                    'access' => 'public',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => '[Originally posted 10 July 2015]',
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => '<strong>Comment: The viruses of Tulloch et al. do not maintain constant codon pair frequencies, and do not distinguish dinucleotide bias from codon pair bias</strong>',
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => 'Bruce Futcher (1#), Oleksandr Gorbatsevych (1), Sam H Shen (3), Charles B Stauft (1,2), Yutong Song (1), Bingyin Wang (1), Janet Leatherwood (1), Justin Gardin (1), Alisa Yurovsky (1), Steffen Mueller (2), Eckard Wimmer (1,2#)',
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => '(1) Dept of Microbiology and Molecular Genetics, Stony Brook University, NY 11790',
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => '(2) Codagenix, 25-108 Health Sciences Dr, Stony Brook, NY 11790',
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => '(3) Present address: Integrated DNA Technologies, Coralville, Iowa 52241',
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => '# Corresponding authors: <a href="mailto:bfutcher@gmail.com">bfutcher@gmail.com</a> / <a href="mailto:eckard.wimmer@stonybrook.edu">eckard.wimmer@stonybrook.edu</a>',
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => "Tulloch et al. (Tulloch et al., 2014) write that \u201c...codon pair deoptimization is an artefact of increases in CpG/UpA dinucleotide frequencies\u201d. We believe there is an error in their approach, which invalidates this conclusion.",
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => "Codon pair bias (Gutman and Hatfield, 1989) and CpG/UpA dinucleotide bias (Beutler et al., 1989, Rothberg and Wimmer, 1981) are two encoding biases. In mammals, codon pairs that have CpG or UpA at the codon-codon junction (i.e., xxC Gxx or xxU Axx codon pairs, C3G1, U3A1) are \u201crare\u201d (i.e., less frequent than expected), as are the dinucleotides CpG and UpA. However, these two biases are not independent \u2013 it is not obvious whether these codon pairs are rare because the dinucleotides are rare, or whether the dinucleotides are rare because the codon pairs are rare, or whether both phenomena are a reflection of some other unknown force.",
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => "We and others have shown that when mammalian viruses are recoded to increase the frequency of very rare codon pairs, this attenuates the virus (Coleman et al., 2008). Because many of the rarest codon pairs in mammals have junctional CpG or UpA, and because of the mathematical linkage between the phenomena, these recoded viruses inevitably have increased frequencies of CpG and UpA dinucleotides. But it is difficult to say what is cause and what is effect. No mechanism for attenuation is known, and the real mechanism of attenuation may not be well-described by either of the terms \u201ccodon pair bias\u201d or \u201cdinucleotide bias\u201d. Our view is that neither term should be taken too seriously, and that a molecular understanding of mechanism is more important than the nomenclature.",
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => "Tulloch et al. (Tulloch et al., 2014) tried to distinguish experimentally between the \u201ccodon pair\u201d and the \u201cdinucleotide\u201d points of view, to see which was causing attenuation. But in evaluating their conclusion, it is essential to understand exactly what it is that they have done. The idea behind their experiment was to recode the \u201cMin-E\u201d version of echovirus 7 (E7) virus so as to keep codon pair frequencies constant, but increase CpG and UpA frequencies, and see whether the resulting test viruses (Min-U and Min-H) were attenuated (which they were). However, it is surprisingly difficult to implement this apparently straight-forward plan. At most positions, CpG and UpA dinucleotides cannot be inserted, because change would lead to a change in the amino acid sequence, or to an unacceptably large change in codon usage. Many positions that can accept recoding with CpG and UpA dinucleotides are at the codon-codon junctions, where a change necessarily also leads to a change in codon pair frequency, so that the two frequencies would change co-ordinately, not independently. How did Tulloch et al. deal with this problem?",
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => "Our inspection of the sequences of the viruses Min-E, U, and H shows that about half of the new CpG/UpA dinucleotides were at codon-codon junctions, and these introduced many very rare codon pairs. That is, in the two test viruses Min-U and Min-H, the very rare C3G1 and U3A1 codon pairs are co-ordinately increased with CpG/UpA dinucleotide frequencies (see Figure 1 below); in fact, the rare codon pairs increase in frequency somewhat faster than the dinucleotides. Because of the co-ordinate increases, the attenuation of the two test viruses is consistent with both the \u201cdinucleotide\u201d and the \u201ccodon pair\u201d point of view, and so the hypotheses are not at all distinguished by these viruses.",
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => "But in contrast with our characterization of Min-E, U, and H in Figure 1, Tulloch et al. (Tulloch et al., 2014) made multiple claims that codon pair frequencies were kept constant in their Min-E, U, and H constructs (e.g., Min-H \u201chas the same CP frequencies as Min-E\u201d). We believe these statements are incorrect and misleading. Inspection reveals that Tulloch et al. have recoded these two viruses to also add, at other positions, other codon pairs that are over-represented (i.e., more frequent than expected) (see Figure 1 of (Futcher et al., 2015)). One can describe under-represented codon pairs as having a negative codon pair score and over-represented codon pairs as having a positive codon pair score (Coleman et al., 2008), and, because of the added positive codon pairs, if one takes all the codon pair scores in Min-U and Min-H and averages them, then that average is similar to the average in Min-E. That is, compared to Min-E, viruses Min-U and Min-H have an increased frequency of both negative (under-represented) and positive (over-represented) codon pairs, so as to achieve a constant average score. But this is not what Tulloch et al. say \u2014 they do not say that the average score is maintained \u2014 instead they say that frequencies are maintained, and this is not correct, and is not at all the same thing. There is no evidence that either the average score, or the number of over-represented codon pairs, has any functional significance (although we and others do use the average score as a book-keeping device when changing the frequency of under-represented codon pairs (Coleman et al., 2008)). Instead, attenuation is likely due to the number of very under-represented codon pairs. This number is significantly raised in both of the test viruses, along with the CpG and UpA dinucleotides. Thus the viruses constructed fail to test the hypothesis, and the conclusion of Tulloch et al. is unwarranted.",
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => 'In their closing remarks Tulloch et al entertain a potential danger should codon pair deoptimized RNA viruses that relative to wild type viruses contain hundreds of silent nucleotide changes be used as vaccines. They ignore that such vaccine candidates, originally investigated only in tissue culture cells (Coleman et al., 2008; Tulloch et al., 2014) will undergo multiple years-long testing in animals and humans for safety, efficacy and genetic stability. Significantly, studies with codon pair deoptimized RNA viruses in tissue culture cells may not yield matching results in experimental animals (Shen et al., 2015).',
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => '<strong>References</strong>',
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => 'BEUTLER, E, GELBART, T, HAN, JH, KOZIOL, JA &amp; BEUTLER, B. 1989. Evolution of the genome and the genetic code: selection at the dinucleotide level by methylation and polyribonucleotide cleavage. Proc Natl Acad Sci U S A, 86, 192-6.',
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => 'COLEMAN, JR, PAPAMICHAIL, D, SKIENA, S, FUTCHER, B, WIMMER, E &amp; MUELLER, S. 2008. Virus attenuation by genome-scale changes in codon pair bias. Science, 320, 1784-7.',
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => 'FUTCHER, B, GORBATSEVYCH, O, SHEN, SH, STAUFT, CB, SONG, Y, WANG, B, LEATHERWOOD, J, GARDIN, J, YUROVSKY, A, MUELLER, S &amp; WIMMER, E. 2015. Reply to Simmonds et al.: Codon pair and dinucleotide bias have not been functionally distinguished. Proc Natl Acad Sci U S A.',
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => 'GUTMAN, GA &amp; HATFIELD, GW. 1989. Nonrandom utilization of codon pairs in Escherichia coli. Proc Natl Acad Sci U S A, 86, 3699-703.',
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => 'ROTHBERG, PG &amp; WIMMER, E. 1981. Mononucleotide and dinucleotide frequencies, and codon usage in poliovirion RNA. Nucleic Acids Res, 9, 6221-9.',
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => 'SHEN, SH, STAUFT, CB, GORBATSEVYCH, O, SONG, Y, WARD, CB, YUROVSKY, A, MUELLER, S, FUTCHER, B &amp; WIMMER, E. 2015. Large-scale recoding of an arbovirus genome to rebalance its insect versus mammalian preference. Proc Natl Acad Sci U S A, 112, 4749-54.',
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => 'TULLOCH, F, ATKINSON, NJ, EVANS, DJ, RYAN, MD &amp; SIMMONDS, P. 2014. RNA virus attenuation by codon pair deoptimisation is an artefact of increases in CpG/UpA dinucleotide frequencies. eLife, 3, e04531.',
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => 'Figure 1. Rare codon pairs and CpG/UpA dinucleotides are increased co-ordinately in the viruses of Tulloch et al.',
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => '<a href="https://cdn.elifesciences.org/annotations-media/2981226593-001-c4da4874752e6ee62725d97cab61f6f58d3e8b2975902d859336f730d1760b61.jpg">https://cdn.elifesciences.org/annotations-media/2981226593-001-c4da4874752e6ee62725d97cab61f6f58d3e8b2975902d859336f730d1760b61.jpg</a>',
                        ],
                    ],
                    'created' => $createdDate,
                    'document' => [
                        'title' => 'title',
                        'uri' => 'uri',
                    ],
                    'parents' => [],
                ],
                new Annotation(
                    'id',
                    "\\[Originally posted 10 July 2015\\]\n\n**Comment: The viruses of Tulloch et al. do not maintain constant codon pair frequencies, and do not distinguish dinucleotide bias from codon pair bias**\n\nBruce Futcher (1#), Oleksandr Gorbatsevych (1), Sam H Shen (3), Charles B Stauft (1,2), Yutong Song (1), Bingyin Wang (1), Janet Leatherwood (1), Justin Gardin (1), Alisa Yurovsky (1), Steffen Mueller (2), Eckard Wimmer (1,2#)\n\n-----\n\n(1) Dept of Microbiology and Molecular Genetics, Stony Brook University, NY 11790\n\n(2) Codagenix, 25-108 Health Sciences Dr, Stony Brook, NY 11790\n\n(3) Present address: Integrated DNA Technologies, Coralville, Iowa 52241\n\n\\# Corresponding authors: [bfutcher@gmail.com](mailto:bfutcher@gmail.com) / [eckard.wimmer@stonybrook.edu](mailto:eckard.wimmer@stonybrook.edu)\n\n-----\n\nTulloch et al. (Tulloch et al., 2014) write that \u201c...codon pair deoptimization is an artefact of increases in CpG/UpA dinucleotide frequencies\u201d. We believe there is an error in their approach, which invalidates this conclusion.\n\nCodon pair bias (Gutman and Hatfield, 1989) and CpG/UpA dinucleotide bias (Beutler et al., 1989, Rothberg and Wimmer, 1981) are two encoding biases. In mammals, codon pairs that have CpG or UpA at the codon-codon junction (i.e., xxC Gxx or xxU Axx codon pairs, C3G1, U3A1) are \u201crare\u201d (i.e., less frequent than expected), as are the dinucleotides CpG and UpA. However, these two biases are not independent \u2013 it is not obvious whether these codon pairs are rare because the dinucleotides are rare, or whether the dinucleotides are rare because the codon pairs are rare, or whether both phenomena are a reflection of some other unknown force.\n\nWe and others have shown that when mammalian viruses are recoded to increase the frequency of very rare codon pairs, this attenuates the virus (Coleman et al., 2008). Because many of the rarest codon pairs in mammals have junctional CpG or UpA, and because of the mathematical linkage between the phenomena, these recoded viruses inevitably have increased frequencies of CpG and UpA dinucleotides. But it is difficult to say what is cause and what is effect. No mechanism for attenuation is known, and the real mechanism of attenuation may not be well-described by either of the terms \u201ccodon pair bias\u201d or \u201cdinucleotide bias\u201d. Our view is that neither term should be taken too seriously, and that a molecular understanding of mechanism is more important than the nomenclature.\n\nTulloch et al. (Tulloch et al., 2014) tried to distinguish experimentally between the \u201ccodon pair\u201d and the \u201cdinucleotide\u201d points of view, to see which was causing attenuation. But in evaluating their conclusion, it is essential to understand exactly what it is that they have done. The idea behind their experiment was to recode the \u201cMin-E\u201d version of echovirus 7 (E7) virus so as to keep codon pair frequencies constant, but increase CpG and UpA frequencies, and see whether the resulting test viruses (Min-U and Min-H) were attenuated (which they were). However, it is surprisingly difficult to implement this apparently straight-forward plan. At most positions, CpG and UpA dinucleotides cannot be inserted, because change would lead to a change in the amino acid sequence, or to an unacceptably large change in codon usage. Many positions that can accept recoding with CpG and UpA dinucleotides are at the codon-codon junctions, where a change necessarily also leads to a change in codon pair frequency, so that the two frequencies would change co-ordinately, not independently. How did Tulloch et al. deal with this problem?\n\nOur inspection of the sequences of the viruses Min-E, U, and H shows that about half of the new CpG/UpA dinucleotides were at codon-codon junctions, and these introduced many very rare codon pairs. That is, in the two test viruses Min-U and Min-H, the very rare C3G1 and U3A1 codon pairs are co-ordinately increased with CpG/UpA dinucleotide frequencies (see Figure 1 below); in fact, the rare codon pairs increase in frequency somewhat faster than the dinucleotides. Because of the co-ordinate increases, the attenuation of the two test viruses is consistent with both the \u201cdinucleotide\u201d and the \u201ccodon pair\u201d point of view, and so the hypotheses are not at all distinguished by these viruses.\n\nBut in contrast with our characterization of Min-E, U, and H in Figure 1, Tulloch et al. (Tulloch et al., 2014) made multiple claims that codon pair frequencies were kept constant in their Min-E, U, and H constructs (e.g., Min-H \u201chas the same CP frequencies as Min-E\u201d). We believe these statements are incorrect and misleading. Inspection reveals that Tulloch et al. have recoded these two viruses to also add, at other positions, other codon pairs that are over-represented (i.e., more frequent than expected) (see Figure 1 of (Futcher et al., 2015)). One can describe under-represented codon pairs as having a negative codon pair score and over-represented codon pairs as having a positive codon pair score (Coleman et al., 2008), and, because of the added positive codon pairs, if one takes all the codon pair scores in Min-U and Min-H and averages them, then that average is similar to the average in Min-E. That is, compared to Min-E, viruses Min-U and Min-H have an increased frequency of both negative (under-represented) and positive (over-represented) codon pairs, so as to achieve a constant average score. But this is not what Tulloch et al. say \u2014 they do not say that the average score is maintained \u2014 instead they say that frequencies are maintained, and this is not correct, and is not at all the same thing. There is no evidence that either the average score, or the number of over-represented codon pairs, has any functional significance (although we and others do use the average score as a book-keeping device when changing the frequency of under-represented codon pairs (Coleman et al., 2008)). Instead, attenuation is likely due to the number of very under-represented codon pairs. This number is significantly raised in both of the test viruses, along with the CpG and UpA dinucleotides. Thus the viruses constructed fail to test the hypothesis, and the conclusion of Tulloch et al. is unwarranted.\n\nIn their closing remarks Tulloch et al entertain a potential danger should codon pair deoptimized RNA viruses that relative to wild type viruses contain hundreds of silent nucleotide changes be used as vaccines. They ignore that such vaccine candidates, originally investigated only in tissue culture cells (Coleman et al., 2008; Tulloch et al., 2014) will undergo multiple years-long testing in animals and humans for safety, efficacy and genetic stability. Significantly, studies with codon pair deoptimized RNA viruses in tissue culture cells may not yield matching results in experimental animals (Shen et al., 2015).\n\n-----\n\n**References**\n\nBEUTLER, E, GELBART, T, HAN, JH, KOZIOL, JA & BEUTLER, B. 1989. Evolution of the genome and the genetic code: selection at the dinucleotide level by methylation and polyribonucleotide cleavage. Proc Natl Acad Sci U S A, 86, 192-6.\n\nCOLEMAN, JR, PAPAMICHAIL, D, SKIENA, S, FUTCHER, B, WIMMER, E & MUELLER, S. 2008. Virus attenuation by genome-scale changes in codon pair bias. Science, 320, 1784-7.\n\nFUTCHER, B, GORBATSEVYCH, O, SHEN, SH, STAUFT, CB, SONG, Y, WANG, B, LEATHERWOOD, J, GARDIN, J, YUROVSKY, A, MUELLER, S & WIMMER, E. 2015. Reply to Simmonds et al.: Codon pair and dinucleotide bias have not been functionally distinguished. Proc Natl Acad Sci U S A.\n\nGUTMAN, GA & HATFIELD, GW. 1989. Nonrandom utilization of codon pairs in Escherichia coli. Proc Natl Acad Sci U S A, 86, 3699-703.\n\nROTHBERG, PG & WIMMER, E. 1981. Mononucleotide and dinucleotide frequencies, and codon usage in poliovirion RNA. Nucleic Acids Res, 9, 6221-9.\n\nSHEN, SH, STAUFT, CB, GORBATSEVYCH, O, SONG, Y, WARD, CB, YUROVSKY, A, MUELLER, S, FUTCHER, B & WIMMER, E. 2015. Large-scale recoding of an arbovirus genome to rebalance its insect versus mammalian preference. Proc Natl Acad Sci U S A, 112, 4749-54.\n\nTULLOCH, F, ATKINSON, NJ, EVANS, DJ, RYAN, MD & SIMMONDS, P. 2014. RNA virus attenuation by codon pair deoptimisation is an artefact of increases in CpG/UpA dinucleotide frequencies. eLife, 3, e04531.\n\n-----\n\nFigure 1. Rare codon pairs and CpG/UpA dinucleotides are increased co-ordinately in the viruses of Tulloch et al.\n\n[![](https://cdn.elifesciences.org/annotations-media/2981226593-001-c4da4874752e6ee62725d97cab61f6f58d3e8b2975902d859336f730d1760b61.jpg)](https://cdn.elifesciences.org/annotations-media/2981226593-001-c4da4874752e6ee62725d97cab61f6f58d3e8b2975902d859336f730d1760b61.jpg)",
                    new DateTimeImmutable($createdDate),
                    new DateTimeImmutable($createdDate),
                    new Annotation\Document('title'),
                    new Annotation\Target('source'),
                    'uri',
                    null,
                    new Annotation\Permissions(Annotation::PUBLIC_GROUP)
                ),
            ],
            'markdown-code' => [
                [
                    'id' => 'id',
                    'access' => 'public',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'To check I understand the requirements here, you want to be able to index a conversation thread (annotation + all replies) as one ES document, and then in response to a query, return a data structure which contains the IDs of matching conversations plus the IDs of matching items (annotation or original reply) within those conversations?',
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => 'So this is essentially the same problem as say, finding out which page matched if you were indexing multi-page documents?',
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => 'Presumably ES can store position information with indexed terms. In that case here is one possible approach: Take all of the original items in the thread and serialize them into a single string - which is indexed with positional information, and separately the offsets of each item within that string are stored as a non-indexed field.',
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => 'eg:',
                        ],
                        [
                            'type' => 'code',
                            'code' => "&quot;content&quot; field: annotation content | first reply | second reply\n&quot;offsets&quot; field: &lt;first reply ID&gt;:&lt;offset of first reply&gt;,&lt;second reply ID&gt;:&lt;offset of second reply&gt;\n",
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => 'When a search query is received, an ES query is performed to find the matching documents and get the offsets of matches within the &quot;content&quot; field. These offsets are then looked up in the &quot;offsets&quot; field to get the thread IDs.',
                        ],
                    ],
                    'created' => $createdDate,
                    'document' => [
                        'title' => 'title',
                        'uri' => 'uri',
                    ],
                    'parents' => [],
                ],
                new Annotation(
                    'id',
                    "To check I understand the requirements here, you want to be able to index a conversation thread (annotation + all replies) as one ES document, and then in response to a query, return a data structure which contains the IDs of matching conversations plus the IDs of matching items (annotation or original reply) within those conversations?\n\nSo this is essentially the same problem as say, finding out which page matched if you were indexing multi-page documents?\n\nPresumably ES can store position information with indexed terms. In that case here is one possible approach: Take all of the original items in the thread and serialize them into a single string - which is indexed with positional information, and separately the offsets of each item within that string are stored as a non-indexed field.\n\neg:\n\n```\n\"content\" field: annotation content | first reply | second reply\n\"offsets\" field: <first reply ID>:<offset of first reply>,<second reply ID>:<offset of second reply>\n```\n\nWhen a search query is received, an ES query is performed to find the matching documents and get the offsets of matches within the \"content\" field. These offsets are then looked up in the \"offsets\" field to get the thread IDs.",
                    new DateTimeImmutable($createdDate),
                    new DateTimeImmutable($createdDate),
                    new Annotation\Document('title'),
                    new Annotation\Target('source'),
                    'uri',
                    null,
                    new Annotation\Permissions(Annotation::PUBLIC_GROUP)
                ),
            ],
            'markdown-mathml' => [
                [
                    'id' => 'id',
                    'access' => 'public',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => '&lt;math xmlns=&quot;http://www.w3.org/1998/Math/MathML&quot;&gt;&lt;mstyle mathcolor=&quot;blue&quot; fontfamily=&quot;serif&quot; displaystyle=&quot;true&quot;&gt;&lt;mi&gt;a&lt;/mi&gt;&lt;msup&gt;&lt;mi&gt;x&lt;/mi&gt;&lt;mn&gt;2&lt;/mn&gt;&lt;/msup&gt;&lt;mo&gt;+&lt;/mo&gt;&lt;mi&gt;b&lt;/mi&gt;&lt;mi&gt;x&lt;/mi&gt;&lt;mo&gt;+&lt;/mo&gt;&lt;mi&gt;c&lt;/mi&gt;&lt;mo&gt;=&lt;/mo&gt;&lt;mn&gt;0&lt;/mn&gt;&lt;/mstyle&gt;&lt;/math&gt;',
                        ],
                    ],
                    'created' => $createdDate,
                    'document' => [
                        'title' => 'title',
                        'uri' => 'uri',
                    ],
                    'parents' => [],
                ],
                new Annotation(
                    'id',
                    '<math xmlns="http://www.w3.org/1998/Math/MathML"><mstyle mathcolor="blue" fontfamily="serif" displaystyle="true"><mi>a</mi><msup><mi>x</mi><mn>2</mn></msup><mo>+</mo><mi>b</mi><mi>x</mi><mo>+</mo><mi>c</mi><mo>=</mo><mn>0</mn></mstyle></math>',
                    new DateTimeImmutable($createdDate),
                    new DateTimeImmutable($createdDate),
                    new Annotation\Document('title'),
                    new Annotation\Target('source'),
                    'uri',
                    null,
                    new Annotation\Permissions(Annotation::PUBLIC_GROUP)
                ),
            ],
            'markdown-latex' => [
                [
                    'id' => 'id',
                    'access' => 'public',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'Math inline (k_{n+1} = n^2 + k_n^2 - k_{n-1})',
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => 'And a block of math for larger equations:',
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => "$$\n\\forall x \\in X,\n\\quad \\exists y\n\\leq \\epsilon\n$$",
                        ],
                    ],
                    'created' => $createdDate,
                    'document' => [
                        'title' => 'title',
                        'uri' => 'uri',
                    ],
                    'parents' => [],
                ],
                new Annotation(
                    'id',
                    $this->lines([
                        'Math inline \\(k_{n+1} = n^2 + k_n^2 - k_{n-1}\\)'.PHP_EOL,
                        'And a block of math for larger equations:'.PHP_EOL,
                        '$$',
                        '\\forall x \\in X,',
                        '\\quad \\exists y',
                        '\\leq \\epsilon',
                        '$$'
                    ]),
                    new DateTimeImmutable($createdDate),
                    new DateTimeImmutable($createdDate),
                    new Annotation\Document('title'),
                    new Annotation\Target('source'),
                    'uri',
                    null,
                    new Annotation\Permissions(Annotation::PUBLIC_GROUP)
                ),
            ],
            'markdown-strip-tags' => [
                [
                    'id' => 'id',
                    'access' => 'public',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'Leading paragraph.',
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => 'iframe: ',
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => 'Trailing paragraph.',
                        ],
                    ],
                    'created' => $createdDate,
                    'document' => [
                        'title' => 'title',
                        'uri' => 'uri',
                    ],
                    'parents' => [],
                ],
                new Annotation(
                    'id',
                    $this->lines([
                        'Leading paragraph.'.PHP_EOL,
                        'iframe: <iframe src="https://elifesciences.org"></iframe>'.PHP_EOL,
                        'Trailing paragraph.',
                    ]),
                    new DateTimeImmutable($createdDate),
                    new DateTimeImmutable($createdDate),
                    new Annotation\Document('title'),
                    new Annotation\Target('source'),
                    'uri',
                    null,
                    new Annotation\Permissions(Annotation::PUBLIC_GROUP)
                ),
            ],
            'markdown-strip-all-tags' => [
                [
                    'id' => 'id',
                    'access' => 'public',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'NOTE: It is not possible to display this content.',
                        ],
                    ],
                    'created' => $createdDate,
                    'document' => [
                        'title' => 'title',
                        'uri' => 'uri',
                    ],
                    'parents' => [],
                ],
                new Annotation(
                    'id',
                    '<iframe src="https://elifesciences.org"></iframe>',
                    new DateTimeImmutable($createdDate),
                    new DateTimeImmutable($createdDate),
                    new Annotation\Document('title'),
                    new Annotation\Target('source'),
                    'uri',
                    null,
                    new Annotation\Permissions(Annotation::PUBLIC_GROUP)
                ),
            ],
        ];
    }

    private function lines(array $lines) {
        return implode(PHP_EOL, $lines);
    }
}
