<?php

namespace Parable\Tests\Components\GetSet;

class InputStreamTest extends \Parable\Tests\Base
{
    public function testRawDataParsedProperly()
    {
        $getSet = $this->setRawSource();
        $this->assertSame("value-from-raw", $getSet->get("test"));
    }

    public function testJsonDataParsedProperly()
    {
        $getSet = $this->setJsonSource();
        $this->assertSame("value-from-json", $getSet->get("test"));
    }

    /**
     * @return \Parable\GetSet\InputStream
     */
    protected function setRawSource()
    {
        return $this->setSource(__DIR__ . "/Files/InputSourceRaw.txt");
    }

    /**
     * @return \Parable\GetSet\InputStream
     */
    protected function setJsonSource()
    {
        return $this->setSource(__DIR__ . "/Files/InputSourceJson.txt");
    }

    /**
     * @param string $path
     *
     * @return \Parable\GetSet\InputStream
     */
    protected function setSource($path)
    {
        $getSet = new \Parable\GetSet\InputStream();
        $this->mockProperty($getSet, "inputSource", $path);
        $getSet->__construct();

        return $getSet;
    }
}
