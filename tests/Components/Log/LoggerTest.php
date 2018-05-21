<?php

namespace Parable\Tests\Components\Log;

class LoggerTest extends \Parable\Tests\Base
{
    /** @var \Parable\Log\Logger */
    protected $logger;

    /** @var string */
    protected $logFile;

    /** @var \Parable\Log\Writer\File|\PHPUnit_Framework_MockObject_MockObject */
    protected $fileWriter;

    /** @var string */
    protected $recentLogLines;

    protected function setUp()
    {
        parent::setUp();

        $this->logFile = $this->testPath->getDir('/tests/test.log');

        $this->logger = new \Parable\Log\Logger();

        $this->fileWriter = $this->createPartialMock(\Parable\Log\Writer\File::class, ['writeToFile', 'createFile']);
    }

    public function testLoggerWithoutWriterThrowsExceptionCallingWrite()
    {
        $this->expectException(\Parable\Log\Exception::class);
        $this->expectExceptionMessage("Can't write without a valid \Log\Writer instance set.");
        $this->logger->write('message');
    }

    public function testLoggerWithoutWriterThrowsExceptionCallingWriteLines()
    {
        $this->expectException(\Parable\Log\Exception::class);
        $this->expectExceptionMessage("Can't writeLines without a valid \Log\Writer instance set.");
        $this->logger->writeLines(['message']);
    }

    public function testLoggerWriteCanHandleAllTypes()
    {
        $this->logger->setWriter(new \Parable\Log\Writer\Console());
        $this->logger->write('message');

        $this->assertSame("message\n", $this->getActualOutputAndClean());

        $this->logger->write(1);
        $this->assertSame("1\n", $this->getActualOutputAndClean());

        $this->logger->write(1.2345);
        $this->assertSame("1.2345\n", $this->getActualOutputAndClean());

        $this->logger->write(true);
        $this->assertSame("true\n", $this->getActualOutputAndClean());

        $this->logger->write(false);
        $this->assertSame("false\n", $this->getActualOutputAndClean());

        $this->logger->write([]);
        $this->assertSame("array (\n)\n", $this->getActualOutputAndClean());

        $this->logger->write(new \stdClass());
        $this->assertSame("stdClass::__set_state(array(\n))\n", $this->getActualOutputAndClean());
    }

    public function testFileWriter()
    {
        $this->fileWriter
            ->method('writeToFile')
            ->willReturnCallback(function ($message) {
                // To prevent having to actually write to a file, we just add everything to a property
                $this->recentLogLines .= $message . PHP_EOL;
            });
        $this->fileWriter
            ->method('createFile')
            ->willReturn(true);

        $this->fileWriter->setLogFile($this->logFile);
        $this->logger->setWriter($this->fileWriter);

        $this->logger->write('test1');
        $this->logger->write('test2');

        $this->logger->writeLines([
            'test3',
            'test4',
        ]);

        $this->assertSame("test1\ntest2\ntest3\ntest4\n", $this->recentLogLines);
    }

    public function testFileWriterDealsWithObjectsArraysBoolsProperly()
    {
        $this->fileWriter
            ->method('writeToFile')
            ->willReturnCallback(function ($message) {
                // To prevent having to actually write to a file, we just add everything to a property
                $this->recentLogLines .= $message . PHP_EOL;
            });

        $this->fileWriter
            ->method('createFile')
            ->willReturn(true);

        $this->fileWriter->setLogFile($this->logFile);
        $this->logger->setWriter($this->fileWriter);

        $this->logger->write(new \stdClass());
        $this->logger->write([]);
        $this->logger->write(false);

        $this->assertSame("stdClass::__set_state(array(\n))\narray (\n)\nfalse\n", $this->recentLogLines);
    }

    public function testFileWriterLoggerThrowsExceptionCallingWriteWithoutLogFileSet()
    {
        $this->expectException(\Parable\Log\Exception::class);
        $this->expectExceptionMessage("No log file set. \Log\Writer\File requires a valid target file.");

        $this->logger->setWriter($this->fileWriter);

        $this->logger->write('stuff');
    }

    public function testFileWriterLoggerThrowsExceptionIfLogfileUnwritable()
    {
        $this->fileWriter
            ->method('createFile')
            ->willReturn(false);

        $this->expectException(\Parable\Log\Exception::class);
        $this->expectExceptionMessage("Log file is not writable.");

        $this->fileWriter->setLogFile("/bla");
    }

    protected function tearDown()
    {
        @unlink($this->logFile);
        parent::tearDown();
    }
}
