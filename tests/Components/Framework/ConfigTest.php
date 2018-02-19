<?php

namespace Parable\Tests\Components\Framework;

class ConfigTest extends \Parable\Tests\Components\Framework\Base
{
    /** @var \Parable\Framework\Config */
    protected $config;

    /** @var \Parable\Tests\TestClasses\Config1 */
    protected $config1;

    /** @var \Parable\Tests\TestClasses\Config2 */
    protected $config2;

    protected function setUp()
    {
        parent::setUp();

        $this->config = \Parable\DI\Container::get(\Parable\Framework\Config::class);

        $this->config1 = new \Parable\Tests\TestClasses\Config1();
        $this->config2 = new \Parable\Tests\TestClasses\Config2();
    }

    public function testLoadFailsSilently()
    {
        $this->assertSame($this->config, $this->config->load());
    }

    public function testLoadFailsSilentlyIfBaseClassDoesNotExist()
    {
        // Set the baseConfigClass to a non-existing class to mimic the default config not existing
        $this->mockProperty($this->config, "mainConfigClass", "Bubaloo");
        $this->assertSame($this->config, $this->config->load());
    }

    public function testSetMainConfigClassThrowsExceptionIfNotExist()
    {
        $this->expectException(\Parable\Framework\Exception::class);
        $this->expectExceptionMessage("Main Config class 'Nope.' does not exist.");

        $this->config->setMainConfigClassName("Nope.");
    }

    public function testGetConfig()
    {
        $this->config->addConfig($this->config1);
        $this->assertSame("primary value", $this->config->get('setting'));
    }

    public function testGetConfigNonExistingValueReturnsNull()
    {
        $this->config->addConfig($this->config1);
        $this->assertNull(null, $this->config->get('setting_does_not_exist'));
    }

    public function testGetNestedConfigValues()
    {
        $this->config->addConfig($this->config1);
        $this->assertSame(
            ['values' => ['rock' => ['so' => ['much' => true]]]],
            $this->config->get('nested')
        );
        $this->assertSame(
            ['rock' => ['so' => ['much' => true]]],
            $this->config->get('nested.values')
        );
        $this->assertSame(
            ['so' => ['much' => true]],
            $this->config->get('nested.values.rock')
        );
        $this->assertSame(
            ['much' => true],
            $this->config->get('nested.values.rock.so')
        );
        $this->assertTrue($this->config->get('nested.values.rock.so.much'));
    }

    public function testGetConfigSetsDataBasedOnAddConfigTiming()
    {
        $this->config->addConfig($this->config1);
        $this->assertSame("primary value", $this->config->get('setting'));

        $this->config->addConfig($this->config2);
        $this->assertSame("secondary value", $this->config->get('setting'));
    }
}
