<?php

namespace tests\eLife\Annotations\Serializer;

use DateTimeImmutable;
use DateTimeZone;
use eLife\Annotations\Serializer\AnnotationNormalizer;
use eLife\ApiSdk\Serializer\Block;
use eLife\ApiSdk\Serializer\NormalizerAwareSerializer;
use eLife\HypothesisClient\Model\Annotation;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

/**
 * @covers \eLife\Annotations\Serializer\AnnotationNormalizer
 */
final class AnnotationNormalizerTest extends PHPUnit_Framework_TestCase
{
    /** @var AnnotationNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new NormalizerAwareSerializer([
            new AnnotationNormalizer(),
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
                            new Annotation\Target\Selector\TextPosition(0, 10),
                            new Annotation\Target\Selector\TextQuote('highlight', 'prefix', 'suffix'),
                            new Annotation\Target\Selector\Range('div[1]', 'div[2]', 10, 300),
                            new Annotation\Target\Selector\Fragment('conforms_to', 'value')
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
                    "   paragraph 1, **with bold text**\n\nparagraph 2",
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
                    "List:\n\n- Item 1\n- Item 2  \n\n1. Item 1\n1. Item 2\n1. Item 3\n\nFinal paragraph.",
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
                    "Opening paragraph\n\n> Quote\n\n> Another quote\n\nClosing paragraph",
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
                            'text' => "[Originally posted 10 July 2015]",
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => "<strong>Comment: The viruses of Tulloch et al. do not maintain constant codon pair frequencies, and do not distinguish dinucleotide bias from codon pair bias</strong>",
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => "Bruce Futcher (1#), Oleksandr Gorbatsevych (1), Sam H Shen (3), Charles B Stauft (1,2), Yutong Song (1), Bingyin Wang (1), Janet Leatherwood (1), Justin Gardin (1), Alisa Yurovsky (1), Steffen Mueller (2), Eckard Wimmer (1,2#)",
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => "(1) Dept of Microbiology and Molecular Genetics, Stony Brook University, NY 11790",
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => "(2) Codagenix, 25-108 Health Sciences Dr, Stony Brook, NY 11790",
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => "(3) Present address: Integrated DNA Technologies, Coralville, Iowa 52241",
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => "# Corresponding authors: <a href=\"mailto:bfutcher@gmail.com\">bfutcher@gmail.com</a> / <a href=\"mailto:eckard.wimmer@stonybrook.edu\">eckard.wimmer@stonybrook.edu</a>",
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
                            'text' => "In their closing remarks Tulloch et al entertain a potential danger should codon pair deoptimized RNA viruses that relative to wild type viruses contain hundreds of silent nucleotide changes be used as vaccines. They ignore that such vaccine candidates, originally investigated only in tissue culture cells (Coleman et al., 2008; Tulloch et al., 2014) will undergo multiple years-long testing in animals and humans for safety, efficacy and genetic stability. Significantly, studies with codon pair deoptimized RNA viruses in tissue culture cells may not yield matching results in experimental animals (Shen et al., 2015).",
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => "<strong>References</strong>",
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => "BEUTLER, E, GELBART, T, HAN, JH, KOZIOL, JA &amp; BEUTLER, B. 1989. Evolution of the genome and the genetic code: selection at the dinucleotide level by methylation and polyribonucleotide cleavage. Proc Natl Acad Sci U S A, 86, 192-6.",
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => "COLEMAN, JR, PAPAMICHAIL, D, SKIENA, S, FUTCHER, B, WIMMER, E &amp; MUELLER, S. 2008. Virus attenuation by genome-scale changes in codon pair bias. Science, 320, 1784-7.",
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => "FUTCHER, B, GORBATSEVYCH, O, SHEN, SH, STAUFT, CB, SONG, Y, WANG, B, LEATHERWOOD, J, GARDIN, J, YUROVSKY, A, MUELLER, S &amp; WIMMER, E. 2015. Reply to Simmonds et al.: Codon pair and dinucleotide bias have not been functionally distinguished. Proc Natl Acad Sci U S A.",
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => "GUTMAN, GA &amp; HATFIELD, GW. 1989. Nonrandom utilization of codon pairs in Escherichia coli. Proc Natl Acad Sci U S A, 86, 3699-703.",
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => "ROTHBERG, PG &amp; WIMMER, E. 1981. Mononucleotide and dinucleotide frequencies, and codon usage in poliovirion RNA. Nucleic Acids Res, 9, 6221-9.",
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => "SHEN, SH, STAUFT, CB, GORBATSEVYCH, O, SONG, Y, WARD, CB, YUROVSKY, A, MUELLER, S, FUTCHER, B &amp; WIMMER, E. 2015. Large-scale recoding of an arbovirus genome to rebalance its insect versus mammalian preference. Proc Natl Acad Sci U S A, 112, 4749-54.",
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => "TULLOCH, F, ATKINSON, NJ, EVANS, DJ, RYAN, MD &amp; SIMMONDS, P. 2014. RNA virus attenuation by codon pair deoptimisation is an artefact of increases in CpG/UpA dinucleotide frequencies. eLife, 3, e04531.",
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => "Figure 1. Rare codon pairs and CpG/UpA dinucleotides are increased co-ordinately in the viruses of Tulloch et al.",
                        ],
                        [
                            'type' => 'paragraph',
                            'text' => "<a href=\"https://cdn.elifesciences.org/annotations-media/2981226593-001-c4da4874752e6ee62725d97cab61f6f58d3e8b2975902d859336f730d1760b61.jpg\">https://cdn.elifesciences.org/annotations-media/2981226593-001-c4da4874752e6ee62725d97cab61f6f58d3e8b2975902d859336f730d1760b61.jpg</a>",
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
                )
            ],
        ];
    }
}
