<?php

namespace Parable\Tests\Components\Filesystem;

class PathTest extends \Parable\Tests\Base
{
    /** @var \Parable\Filesystem\Path */
    protected $path;

    protected function setUp()
    {
        parent::setUp();

        $this->path = new \Parable\Filesystem\Path();
    }

    public function testSetAndGetBaseDir()
    {
        $this->path->setBaseDir("This is not a valid basedir.");
        $this->assertSame("This is not a valid basedir.", $this->path->getBaseDir());
    }

    public function testGetDir()
    {
        $this->path->setBaseDir("basedir/");
        $this->assertSame("basedir/stuff", $this->path->getDir("stuff"));
    }

    public function testBaseDirAppliedCorrectlyIfLocalFileExists()
    {
        $path = \Parable\DI\Container::get(\Parable\Filesystem\Path::class);

        $expectedPath = $path->getBaseDir() . DIRECTORY_SEPARATOR . "PathTest.php";

        $this->assertSame($expectedPath, $path->getDir("PathTest.php"));
    }
}
