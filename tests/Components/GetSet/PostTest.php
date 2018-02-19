<?php

namespace Parable\Tests\Components\GetSet;

class PostTest extends \Parable\Tests\Base
{
    /** @var \Parable\GetSet\Post */
    protected $getSet;

    protected function setUp()
    {
        parent::setUp();

        $this->getSet = new \Parable\GetSet\Post();
    }

    public function testGetResource()
    {
        $this->assertSame("_POST", $this->getSet->getResource());
    }

    public function testGetSetOnResource()
    {
        $this->getSet->set("stuff", "here");

        $this->assertSame(
            $this->getSet->getAll(),
            $_POST
        );

        $this->assertSame(
            [
                "stuff" => "here",
            ],
            $_POST
        );
    }
}
