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
        $response = \Parable\DI\Container::createAll(\Parable\Http\Response::class);
        $response->setOutput($this->json);

        $response->setContent([
            'value'     => 'stuff',
            'secondary' => 'what now',
        ]);

        $content = $this->json->prepare($response);

        $this->assertSame('{"value":"stuff","secondary":"what now"}', $content);
    }

    public function testInvalidJsonStringStaysString()
    {
        $response = \Parable\DI\Container::createAll(\Parable\Http\Response::class);
        $response->setOutput($this->json);

        $response->setContent("{[{[}]}]");

        $content = $this->json->prepare($response);

        $this->assertSame('"{[{[}]}]"', $content);
    }

    /**
     * @dataProvider dpDataTypes
     */
    public function testAcceptsContentSaysYesToEverything($type)
    {
        $this->assertTrue($this->json->acceptsContent($type));
    }

    public function dpDataTypes()
    {
        return [
            [null],
            ["string"],
            [1337],
            [true],
            [new \stdClass()],
            [["array"]],
        ];
    }
}
