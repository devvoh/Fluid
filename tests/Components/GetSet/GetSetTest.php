<?php

namespace Parable\Tests\Components\GetSet;

class GetSetTest extends \Parable\Tests\Base
{
    /** @var \Parable\Tests\TestClasses\TestGetSet */
    protected $getSet;

    protected function setUp()
    {
        parent::setUp();

        $this->getSet = new \Parable\Tests\TestClasses\TestGetSet();
    }

    public function testSetAndGetResource()
    {
        $this->assertSame('test', $this->getSet->getResource());

        $this->getSet->setResource("what");

        $this->assertSame('what', $this->getSet->getResource());
    }

    public function testSetAllGetAll()
    {
        $this->getSet->setAll([
            'key1' => 'yo1',
            'key2' => 'yo2',
        ]);

        $this->assertSame(
            [
                'key1' => 'yo1',
                'key2' => 'yo2',
            ],
            $this->getSet->getAll()
        );
    }

    public function testGetAllAndReset()
    {
        $this->getSet->setAll([
            'key1' => 'yo1',
            'key2' => 'yo2',
        ]);

        $this->assertSame(
            [
                'key1' => 'yo1',
                'key2' => 'yo2',
            ],
            $this->getSet->getAllAndReset()
        );

        $this->assertSame([], $this->getSet->getAll());
    }

    public function testGetAndRemove()
    {
        $this->getSet->setAll([
            'key1' => 'yo1',
            'key2' => 'yo2',
        ]);

        $this->assertSame(
            'yo1',
            $this->getSet->getAndRemove('key1')
        );

        $this->assertSame(
            [
                'key2' => 'yo2',
            ],
            $this->getSet->getAll()
        );
        $this->assertSame(1, $this->getSet->count());
    }

    public function testRemoveNonExistingKeyDoesNothing()
    {
        $this->getSet->setMany([
            'key1' => 'yo1',
            'key2' => 'yo2',
            'key3' => 'yo3',
        ]);

        $this->assertSame(3, $this->getSet->count());

        $this->assertInstanceOf(
            \Parable\GetSet\Base::class,
            $this->getSet->remove("stuff")
        );

        $this->assertSame(3, $this->getSet->count());
    }

    public function testSetSpecificGetSpecificAndGetAll()
    {
        $this->getSet->set('key1', 'yo1');
        $this->getSet->set('key2', 'yo2');
        $this->getSet->set('key3', 'yo3');

        $this->assertSame('yo3', $this->getSet->get('key3'));

        $this->assertSame(
            [
                'key1' => 'yo1',
                'key2' => 'yo2',
                'key3' => 'yo3',
            ],
            $this->getSet->getAll()
        );
    }

    public function testSetAllVersusSetMany()
    {
        $this->assertCount(0, $this->getSet->getAll());

        $this->getSet->setAll([
            'temp1' => 'yo1',
            'temp2' => 'yo2',
            'temp3' => 'yo3',
        ]);

        $this->assertCount(3, $this->getSet->getAll());

        $this->assertSame(
            [
                'temp1' => 'yo1',
                'temp2' => 'yo2',
                'temp3' => 'yo3',
            ],
            $this->getSet->getAll()
        );

        // setAll() overwrites all values, discarding the pre-existing ones
        $this->getSet->setAll([
            'key1' => 'yo1',
            'key2' => 'yo2',
        ]);

        $this->assertSame(
            [
                'key1' => 'yo1',
                'key2' => 'yo2',
            ],
            $this->getSet->getAll()
        );

        // setMany() overwrites all values passed if they exist, but leaves pre-existing ones intact
        $this->getSet->setMany([
            'key1' => 'this is new',
            'key3' => 'this is new as well',
        ]);

        $this->assertSame(
            [
                'key1' => 'this is new',
                'key2' => 'yo2',
                'key3' => 'this is new as well',
            ],
            $this->getSet->getAll()
        );
    }

    public function testGetAllReturnsEmptyArrayIfNoResourceSet()
    {
        $getset = new \Parable\Tests\TestClasses\TestGetSetNoResource();
        $this->assertSame([], $getset->getAll());
    }

    public function testGetSetAndRemoveWithHierarchalKeys()
    {
        $this->getSet->set("one", ["this" => "should stay"]);
        $this->getSet->set("one.two.three.four", "totally nested, yo");

        $this->assertSame(
            [
                "this" => "should stay",
                "two" => [
                    "three" => [
                        "four" => "totally nested, yo",
                    ],
                ],
            ],
            $this->getSet->get("one")
        );

        $this->assertSame(
            [
                "one" => [
                    "this" => "should stay",
                    "two" => [
                        "three" => [
                            "four" => "totally nested, yo",
                        ],
                    ],
                ],
            ],
            $this->getSet->getAll()
        );

        $this->getSet->remove("one.this");

        $this->assertSame(
            [
                "one" => [
                    "two" => [
                        "three" => [
                            "four" => "totally nested, yo",
                        ],
                    ],
                ],
            ],
            $this->getSet->getAll()
        );

        $this->assertSame(
            [
                "four" => "totally nested, yo",
            ],
            $this->getSet->getAndRemove("one.two.three")
        );

        // And since "three" is now removed, "two" will be empty.
        $this->assertSame(
            [
                "one" => [
                    "two" => [
                    ],
                ],
            ],
            $this->getSet->getAll()
        );
    }

    public function testRemoveHierarchalKey()
    {
        $this->getSet->set("one.two.three", "totally");
        $this->getSet->set("one.two.four", "also");

        $this->assertCount(2, $this->getSet->get("one.two"));

        $this->getSet->remove("one.two.three");

        $this->assertCount(1, $this->getSet->get("one.two"));
        $this->assertTrue(is_array($this->getSet->get("one.two")));

        $this->getSet->remove("one.two");

        $this->assertNull($this->getSet->get("one.two"));

        // But one should be untouched and still an array
        $this->assertTrue(is_array($this->getSet->get("one")));
    }

    public function testGetNonExistingKeyReturnsDefault()
    {
        // Test non-existing should still be null
        $this->assertNull($this->getSet->get("nope"));

        // But with default it should be default
        $this->assertEquals("default", $this->getSet->get("nope", "default"));

        // Same for nested
        $this->getSet->set("this", ["totally" => "exists"]);

        $this->assertNull($this->getSet->get("this.doesn't"));

        $this->assertEquals([], $this->getSet->get("this.doesn't", []));
    }
}
