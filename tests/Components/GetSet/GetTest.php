<?php

namespace Parable\Tests\Components\GetSet;

class GetTest extends \Parable\Tests\Base
{
    /** @var \Parable\GetSet\Get */
    protected $getSet;

    protected function setUp()
    {
        parent::setUp();

        $this->getSet = new \Parable\GetSet\Get();
    }

    public function testGetResource()
    {
        $this->assertSame("_GET", $this->getSet->getResource());
    }

    public function testGetSetOnResource()
    {
        $this->getSet->set("stuff", "here");

        $this->assertSame(
            $this->getSet->getAll(),
            $_GET
        );

        $this->assertSame(
            [
                "stuff" => "here",
            ],
            $_GET
        );
    }

    public function testSetAndRemove()
    {
        $this->getSet->set('test', 'value');

        $this->assertSame('value', $this->getSet->get('test'));

        $this->getSet->remove('test');

        $this->assertNull($this->getSet->get('test'));
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->getSet->reset();
    }
}
