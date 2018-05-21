<?php

namespace Parable\Tests\Components\Framework;

class InitLoaderTest extends \Parable\Tests\Components\Framework\Base
{
    /** @var \Parable\Framework\Loader\InitLoader */
    protected $initLoader;

    public function setUp()
    {
        parent::setUp();

        $this->initLoader = \Parable\DI\Container::create(\Parable\Framework\Loader\InitLoader::class);
    }

    public function testLoad()
    {
        $this->initLoader->load([
            \Parable\Tests\TestClasses\Init\TestEcho::class
        ]);

        $this->assertSame("This init was loaded.", $this->getActualOutputAndClean());
    }
}
