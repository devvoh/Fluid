<?php

namespace Parable\Tests;

abstract class Base extends \PHPUnit\Framework\TestCase
{
    /** @var \Parable\Filesystem\Path */
    protected $testPath;

    protected function setUp()
    {
        parent::setUp();

        // This key might be handy to have
        $GLOBALS['_SESSION'] = [];
        $this->testPath = \Parable\DI\Container::create(\Parable\Filesystem\Path::class);
        $this->testPath->setBaseDir(__DIR__ . DS . "..");
    }

    /**
     * Set $value on $object->$propertyName, even if it's private
     * or protected.
     *
     * @param object $object
     * @param string $propertyName
     * @param mixed  $value
     */
    public function mockProperty($object, $propertyName, $value)
    {
        $reflectionClass = new \ReflectionClass($object);

        $property = $reflectionClass->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $value);
        $property->setAccessible(false);
    }

    /**
     * Makes the $propertyName publicly accessible.
     *
     * @param object $object
     * @param string $propertyName
     *
     * @return mixed
     */
    public function liberateProperty($object, $propertyName)
    {
        $reflectionClass = new \ReflectionClass($object);

        $property = $reflectionClass->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($object);
    }

    /**
     * Make all propertNames passed publicly accessible.
     *
     * @param object   $object
     * @param string[] $propertyNames
     */
    public function liberateProperties($object, array $propertyNames)
    {
        foreach ($propertyNames as $propertyName) {
            $this->liberateProperty($object, $propertyName);
        }
    }

    /**
     * Returns the actual output form the default PHPUnit output buffer,
     * and cleans 1(!) level, clearing the most recent buffer level.
     *
     * @return string
     */
    public function getActualOutputAndClean()
    {
        $content = parent::getActualOutput();
        ob_clean();
        return $content;
    }

    /**
     * @return array
     */
    public function dpTrueFalse()
    {
        return [
            [true],
            [false],
        ];
    }

    protected function tearDown()
    {
        parent::tearDown();

        \Parable\DI\Container::clearExcept([\Parable\Filesystem\Path::class]);
    }
}
