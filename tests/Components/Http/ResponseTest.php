<?php

namespace Parable\Tests\Components\Http;

class ResponseTest extends \Parable\Tests\Base
{
    /** @var \Parable\Http\Response */
    protected $response;

    /** @var \Parable\Http\Response|\PHPUnit_Framework_MockObject_MockObject */
    protected $responseMock;

    protected function setUp()
    {
        parent::setUp();

        $this->responseMock = $this->createPartialMock(\Parable\Http\Response::class, ['terminate']);
        $this->responseMock->__construct(\Parable\DI\Container::get(\Parable\Http\Request::class));

        // Response should not actually terminate
        $this->response = \Parable\DI\Container::createAll(\Parable\Http\Response::class);
        $this->response->setShouldTerminate(false);
    }

    public function testGetHttpCode()
    {
        $this->assertSame(200, $this->response->getHttpCode());
    }

    public function testSetHttpCode()
    {
        $this->response->setHttpCode(404);
        $this->assertSame(404, $this->response->getHttpCode());
    }

    public function testSetInvalidHttpCodeThrowsException()
    {
        $this->expectException(\Parable\Http\Exception::class);
        $this->expectExceptionMessage("Invalid HTTP code set: '9001'");

        $this->response->setHttpCode(9001);
    }

    public function testGetHttpCodeText()
    {
        $this->assertSame("OK", $this->response->getHttpCodeText());

        $this->response->setHttpCode(404);
        $this->assertSame("Not Found", $this->response->getHttpCodeText());

        $this->response->setHttpCode(418);
        $this->assertSame("I'm a teapot", $this->response->getHttpCodeText());
    }

    public function testGetContentType()
    {
        $this->assertSame("text/html", $this->response->getContentType());
    }

    public function testSetContentType()
    {
        $this->response->setContentType('text/css');
        $this->assertSame("text/css", $this->response->getContentType());
    }

    public function testSetOutput()
    {
        $this->assertSame("text/html", $this->response->getContentType());

        $this->response->setOutput(new \Parable\Http\Output\Json());

        $this->assertSame("application/json", $this->response->getContentType());
    }

    public function testSetGetContent()
    {
        $this->response->setContent("This is content.");
        $this->assertSame("This is content.", $this->response->getContent());
    }

    public function testClearContent()
    {
        $this->response->setContent("This is content.");
        $this->assertSame("This is content.", $this->response->getContent());

        $this->response->clearContent();
        $this->response->appendContent("New!");

        $this->assertSame("New!", $this->response->getContent());
    }

    public function testHeaderAndFooterContent()
    {
        $this->assertEmpty($this->response->getHeaderContent());

        $this->response->setHeaderContent("<html>");
        $this->response->setFooterContent("</html>");

        $this->response->setContent("Stuff goes here.");

        $this->assertSame("Stuff goes here.", $this->response->getContent());

        $this->response->send();

        $this->assertSame("<html>Stuff goes here.</html>", $this->getActualOutputAndClean());
    }

    public function testAppendAndPrependContent()
    {
        $this->response->setContent('yo2');
        $this->response->appendContent('yo3');
        $this->response->prependContent('yo1');

        $this->assertSame("yo1yo2yo3", $this->response->getContent());
    }

    public function testAppendAndPrependArrayContent()
    {
        $this->response->setContent(['array2']);
        $this->response->appendContent('array3');
        $this->response->prependContent('array1');

        $this->assertSame(
            [
                0 => 'array1',
                1 => 'array2',
                2 => 'array3',
            ],
            $this->response->getContent()
        );
    }

    public function testSend()
    {
        $this->response->setContent("This is content.");
        $this->response->send();

        $content = $this->getActualOutputAndClean();

        $this->assertSame("This is content.", $content);
    }

    public function testSendWithJsonAndOutputBufferingEnabled()
    {
        $this->response->startOutputBuffer();
        $this->response->setOutput(new \Parable\Http\Output\Json());
        $this->response->setContent([
            'test' => 1,
        ]);
        $this->response->send();

        $content = $this->getActualOutputAndClean();

        $this->assertSame('{"test":1}', $content);
    }

    public function testSendClosesOutputbuffer()
    {
        $this->response->startOutputBuffer();
        echo "1... ";

        $this->response->setContent("This is content.");

        echo "2... ";

        $this->response->send();

        $content = $this->getActualOutputAndClean();

        $this->assertSame("1... 2... This is content.", $content);
    }

    public function testOutputBuffering()
    {
        $this->response->startOutputBuffer();
        echo "This should not show!";
        $this->response->returnOutputBuffer();

        $this->assertSame(null, $this->response->getContent());
    }

    public function testStopOutputBuffering()
    {
        $this->response->startOutputBuffer();

        $this->assertTrue($this->response->isOutputBufferingEnabled());

        $this->response->stopOutputBuffer();

        $this->assertFalse($this->response->isOutputBufferingEnabled());
    }

    public function testStopAllOutputBuffering()
    {
        $this->response->startOutputBuffer();
        $this->response->startOutputBuffer();
        $this->response->startOutputBuffer();
        $this->response->startOutputBuffer();
        $this->response->startOutputBuffer();

        $this->assertTrue($this->response->isOutputBufferingEnabled());

        $this->response->stopOutputBuffer();

        // One output buffer stop isn't enough
        $this->assertTrue($this->response->isOutputBufferingEnabled());

        // But this one is.
        $this->response->stopAllOutputBuffers();

        $this->assertFalse($this->response->isOutputBufferingEnabled());
    }

    public function testReturnOutputBufferReturnsEmptyStringIfNotStarted()
    {
        $this->assertSame("", $this->response->returnOutputBuffer());
    }

    public function testReturnAllOutputBufferReturnsEmptyStringIfNotStarted()
    {
        $this->assertSame("", $this->response->returnAllOutputBuffers());
    }

    public function testOutputBufferingChecking()
    {
        $this->assertFalse($this->response->isOutputBufferingEnabled());

        $this->response->startOutputBuffer();

        $this->assertTrue($this->response->isOutputBufferingEnabled());

        $this->response->returnOutputBuffer();

        $this->assertFalse($this->response->isOutputBufferingEnabled());
    }

    public function testSetGetHeader()
    {
        $this->assertCount(0, $this->response->getHeaders());

        $this->response->setHeader('header1', 'yo1');
        $this->response->setHeader('header2', 'yo2');

        $this->assertCount(2, $this->response->getHeaders());
        $this->assertSame(
            [
                'header1' => 'yo1',
                'header2' => 'yo2',
            ],
            $this->response->getHeaders()
        );

        $this->assertSame("yo1", $this->response->getHeader("header1"));
    }

    public function testSetHeaders()
    {
        $this->assertCount(0, $this->response->getHeaders());

        $this->response->setHeaders([
            "header1" => "yo1",
            "header2" => "yo2",
        ]);

        $this->assertCount(2, $this->response->getHeaders());
        $this->assertSame(
            [
                'header1' => 'yo1',
                'header2' => 'yo2',
            ],
            $this->response->getHeaders()
        );

        $this->assertSame("yo1", $this->response->getHeader("header1"));
    }

    public function testSetAndRemoveHeader()
    {
        $this->response->setHeaders([
            "remove" => "me",
            "leave" => "me",
        ]);

        $this->assertCount(2, $this->response->getHeaders());

        $this->response->removeHeader("remove");

        $this->assertCount(1, $this->response->getHeaders());
    }

    public function testClearHeaders()
    {
        $this->response->setHeaders([
            "remove" => "me",
            "leave" => "me",
        ]);

        $this->assertCount(2, $this->response->getHeaders());

        $this->response->clearHeaders();

        $this->assertCount(0, $this->response->getHeaders());
    }

    public function testGetInvalidHeaderReturnsNull()
    {
        $this->assertNull($this->response->getHeader('la-dee-dah'));
    }

    public function testRedirect()
    {
        // The only way to test this is to see if terminate is called
        $this->responseMock->expects($this->once())->method('terminate');
        $this->responseMock->redirect('http://www.test.dev/redirected');
    }

    public function testOutputPrepareReturningNonStringValueThrowsException()
    {
        $this->expectException(\Parable\Http\Exception::class);
        $this->expectExceptionMessage(
            "Output class 'Parable\Tests\TestClasses\Http\FaultyOutput' did not result in string or null content."
        );

        $this->response->setOutput(new \Parable\Tests\TestClasses\Http\FaultyOutput());
        $this->response->send();
    }
}
