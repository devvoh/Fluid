<?php

namespace Parable\Tests\Components\GetSet;

class SessionTest extends \Parable\Tests\Base
{
    /** @var \Parable\GetSet\Session */
    protected $getSet;

    protected function setUp()
    {
        parent::setUp();

        $this->getSet = new \Parable\GetSet\Session();
    }

    public function testGetResource()
    {
        $this->assertSame("_SESSION", $this->getSet->getResource());
    }

    public function testGetSetOnResource()
    {
        $this->getSet->set("stuff", "here");

        $this->assertSame(
            $this->getSet->getAll(),
            $_SESSION
        );

        $this->assertSame(
            [
                "stuff" => "here",
            ],
            $_SESSION
        );
    }
}
