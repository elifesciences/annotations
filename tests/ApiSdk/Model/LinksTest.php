<?php

namespace tests\eLife\HypothesisClient\ApiSdk\Model;

use eLife\HypothesisClient\ApiSdk\Model\Links;
use PHPUnit_Framework_TestCase;

final class LinksTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_an_incontext_link()
    {
        $with = new Links('incontext');

        $this->assertEquals('incontext', $with->getIncontext());
    }

    /**
     * @test
     */
    public function it_may_have_a_json_link()
    {
        $with = new Links('incontext', 'json');
        $withOut = new Links('incontext', null);

        $this->assertEquals('json', $with->getJson());
        $this->assertNull($withOut->getJson());
    }

    /**
     * @test
     */
    public function it_may_have_a_html_link()
    {
        $with = new Links('incontext', null, 'html');
        $withOut = new Links('incontext', null, null);

        $this->assertEquals('html', $with->getHtml());
        $this->assertNull($withOut->getHtml());
    }

    /**
     * @test
     */
    public function it_casts_to_a_string()
    {
        $with = new Links('incontext');

        $this->assertEquals('incontext', $with->toString());
    }
}
