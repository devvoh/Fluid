<?php

namespace Parable\Tests\Components\Http;

class RequestTest extends \Parable\Tests\Base
{
    /** @var \Parable\Http\Request */
    protected $request;

    protected function setUp()
    {
        parent::setUp();

        // We need to set up the $_SERVER array first
        $_SERVER['REQUEST_METHOD'] = "GET";
        $_SERVER['HTTP_HOST'] = "test.dev";
        $_SERVER['REQUEST_URI'] = "/folder/being/requested";
        $_SERVER['SCRIPT_NAME'] = "stuff";

        $this->request = new \Parable\Http\Request();

        $this->mockProperty($this->request, 'headers', ['testkey' => 'testvalue']);
    }

    public function testGetProtocol()
    {
        // Default is HTTP/1.1, if, for example, SERVER_PROTOCOL is unset.
        $this->assertSame("HTTP/1.1", $this->request->getProtocol());

        $_SERVER['SERVER_PROTOCOL'] = "HTTP/2.0";

        $this->assertSame("HTTP/2.0", $this->request->getProtocol());

        // In CLI mode, this value shouldn't be here.x
        unset($_SERVER['SERVER_PROTOCOL']);
    }

    public function testGetRequestUrl()
    {
        $this->assertSame("/folder/being/requested", $this->request->getRequestUrl());
    }

    public function testGetScriptName()
    {
        $this->assertSame("stuff", $this->request->getScriptName());
    }

    public function testGetMethod()
    {
        $this->assertSame("GET", $this->request->getMethod());
    }

    public function testGetBody()
    {
        // Of course it's empty, since we don't have anything in php://input
        $this->assertEmpty($this->request->getBody());
    }

    public function testGetHttpHost()
    {
        $oldServer = $_SERVER;

        $_SERVER = ["HTTP_HOST" => "httphost!"];
        $this->assertSame("httphost!", $this->request->getHttpHost());

        $_SERVER = ["SERVER_NAME" => "httphost!"];
        $this->assertSame("httphost!", $this->request->getHttpHost());

        $_SERVER = ["SERVER_NAME" => "httphost!", "HTTP_HOST" => "httphost!"];
        $this->assertSame("httphost!", $this->request->getHttpHost());

        $_SERVER = [];
        $this->assertNull($this->request->getHttpHost());

        $_SERVER = $oldServer;
    }

    public function testGetCurrentUrl()
    {
        $this->assertSame("http://test.dev/folder/being/requested", $this->request->getCurrentUrl());
    }

    public function testIsMethod()
    {
        $this->assertTrue($this->request->isMethod('GET'));
        $this->assertFalse($this->request->isMethod('POST'));
        $this->assertFalse($this->request->isMethod('PUT'));
        $this->assertFalse($this->request->isMethod('DELETE'));
        $this->assertFalse($this->request->isMethod('PATCH'));
    }

    public function testIsGet()
    {
        $this->assertTrue($this->request->isGet());
    }

    public function testIsPost()
    {
        $this->assertFalse($this->request->isPost());
    }

    public function testIsPut()
    {
        $this->assertFalse($this->request->isPut());
    }

    public function testIsDelete()
    {
        $this->assertFalse($this->request->isDelete());
    }

    public function testIsPatch()
    {
        $this->assertFalse($this->request->isPatch());
    }

    public function testGetHeader()
    {
        $this->assertSame('testvalue', $this->request->getHeader('testkey'));
    }

    public function testGetHeaderReturnsNullOnNonExistingKey()
    {
        $this->assertNull($this->request->getHeader('blabla'));
    }

    public function testGetHeaders()
    {
        $this->assertSame(
            [
                'testkey' => 'testvalue',
            ],
            $this->request->getHeaders()
        );
    }

    /**
     * @dataProvider dpRequestScheme
     * @param $scheme
     */
    public function testGetSchemeWithAllPossibilities($scheme)
    {
        $_SERVER = [];
        $this->assertSame("http", $this->request->getScheme());

        $_SERVER = ['REQUEST_SCHEME' => $scheme];
        $this->assertSame($scheme, $this->request->getScheme());

        $_SERVER = ['REDIRECT_REQUEST_SCHEME' => $scheme];
        $this->assertSame($scheme, $this->request->getScheme());

        $_SERVER = ['HTTP_X_FORWARDED_PROTO' => $scheme];
        $this->assertSame($scheme, $this->request->getScheme());

        $_SERVER = ['HTTPS' => ($scheme == 'http' ? 'off' : 'on')];
        $this->assertSame($scheme, $this->request->getScheme());

        $_SERVER = ['SERVER_PORT' => ($scheme == 'http' ? 80 : 443)];
        $this->assertSame($scheme, $this->request->getScheme());

        unset($_SERVER['SERVER_PORT']);

        $this->assertEmpty($_SERVER);
        $this->assertSame("http", $this->request->getScheme());
    }

    public function dpRequestScheme()
    {
        return [
            ['http'],
            ['https'],
        ];
    }
}
