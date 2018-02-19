<?php

namespace Parable\Tests\Components\GetSet;

class InternalTest extends \Parable\Tests\Base
{
    /** @var \Parable\GetSet\Internal */
    protected $getSet;

    protected function setUp()
    {
        parent::setUp();

        $this->getSet = new \Parable\GetSet\Internal();
    }

    public function testGetResource()
    {
        $this->assertSame("parable_internal", $this->getSet->getResource());
    }

    public function testGetSetOnResource()
    {
        $this->getSet->set("stuff", "here");

        $this->assertSame(
            ["stuff" => "here"],
            $this->getSet->getAll()
        );
    }
}
