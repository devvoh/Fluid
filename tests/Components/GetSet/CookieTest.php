<?php

namespace Parable\Tests\Components\GetSet;

class CookieTest extends \Parable\Tests\Base
{
    /** @var \Parable\GetSet\Cookie */
    protected $getSet;

    protected function setUp()
    {
        parent::setUp();

        $this->getSet = new \Parable\GetSet\Cookie();
    }

    public function testGetResource()
    {
        $this->assertSame("_COOKIE", $this->getSet->getResource());
    }

    public function testGetSetOnResource()
    {
        $this->getSet->set("stuff", "here");

        $this->assertSame(
            $this->getSet->getAll(),
            $_COOKIE
        );

        $this->assertSame(
            [
                "stuff" => "here",
            ],
            $_COOKIE
        );
    }
}
