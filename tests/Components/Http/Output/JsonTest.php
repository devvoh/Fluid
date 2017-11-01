<?php

namespace Parable\Tests\Components\Output;

class JsonTest extends \Parable\Tests\Base
{
    /** @var \Parable\Http\Output\Json */
    protected $json;

    protected function setUp()
    {
        parent::setUp();

        $this->json = new \Parable\Http\Output\Json();
    }

    public function testInit()
    {
        $response = \Parable\DI\Container::createAll(\Parable\Http\Response::class);

        $this->assertSame('text/html', $response->getContentType());

        $response->setOutput($this->json);

        $this->assertSame('application/json', $response->getContentType());
    }

    public function testPrepare()
    {
        /** @var \Parable\Http\Response $response */
        $response = \Parable\DI\Container::createAll(\Parable\Http\Response::class);
        $response->setOutput($this->json);

        $response->setContent([
            'value'     => 'stuff',
            'secondary' => 'what now',
        ]);

        $this->json->prepare($response);

        $this->assertSame('{"value":"stuff","secondary":"what now"}', $response->getContent());
    }

    public function testInvalidJsonStringStaysString()
    {
        /** @var \Parable\Http\Response $response */
        $response = \Parable\DI\Container::createAll(\Parable\Http\Response::class);
        $response->setOutput($this->json);

        $response->setContent("{[{[}]}]");

        $this->json->prepare($response);

        $this->assertSame('"{[{[}]}]"', $response->getContent());
    }
}
