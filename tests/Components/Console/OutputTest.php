<?php

namespace Parable\Tests\Components\Console;

class OutputTest extends \Parable\Tests\Base
{
    /** @var \Parable\Console\Output|\PHPUnit_Framework_MockObject_MockObject */
    protected $output;

    /** @var string */
    protected $defaultTag = "\e[0m";

    protected function setUp()
    {
        parent::setUp();

        // We mock out parseTags, because it adds too many escape codes. We'll test parseTags concretely later.
        $this->output = $this->createPartialMock(\Parable\Console\Output::class, ['parseTags', 'isInteractiveShell']);

        $this->output
            ->method('parseTags')
            ->withAnyParameters()
            ->willReturnCallback(function ($string) {
                return $string . $this->defaultTag;
            });

        // Make sure Output always thinks it's not in an interactive shell
        $this->output
            ->method('isInteractiveShell')
            ->withAnyParameters()
            ->willReturn(false);
    }

    /**
     * NOTE: All write actions are appended with the default Tag from Output's tags array.
     *
     * Use only this method to compare actual output to string values written.
     *
     * @param string $expected
     * @param string $actual
     */
    protected function assertSameWithTag($expected, $actual)
    {
        $expected = $this->addTag($expected);
        $this->assertSame($expected, $actual);
    }

    /**
     * Add the default tag where it's supposed to go.
     *
     * @param string $value
     * @param int    $amount
     * @return mixed|string
     */
    protected function addTag($value, $amount = 1)
    {
        $defaultTag = str_repeat($this->defaultTag, $amount);
        if (strpos($value, "\n") !== false) {
            // If there's new lines, the default tag is placed just before the newline.
            // At the end of the string, there won't be another default tag.
            $value = str_replace("\n", "{$defaultTag}\n", $value);
        } else {
            // If this is just a line with no newline, there will be a default tag at the end
            $value = $value . $defaultTag;
        }
        return $value;
    }

    public function testWrite()
    {
        $this->output->write('OK');
        $content = $this->getActualOutputAndClean();

        $this->assertSameWithTag("OK", $content);
    }

    public function testWriteln()
    {
        $this->output->writeln('OK');
        $content = $this->getActualOutputAndClean();

        $this->assertSameWithTag("OK\n", $content);
    }

    public function testWritelnWithArray()
    {
        $this->output->writeln([
            'line1',
            'line2'
        ]);
        $content = $this->getActualOutputAndClean();

        $this->assertSameWithTag("line1\nline2\n", $content);
    }

    public function testNewline()
    {
        // Just one.
        $this->output->newline();
        $this->assertSame("\n", $this->getActualOutputAndClean());

        // Now multiple
        $this->output->newline(3);
        $this->assertSame("\n\n\n", $this->getActualOutputAndClean());
    }

    public function testCursorForward()
    {
        $this->output->cursorForward(1);
        $this->assertSameWithTag("\e[1C", $this->getActualOutputAndClean());
    }

    public function testCursorBackward()
    {
        $this->output->cursorBackward(1);
        $this->assertSameWithTag("\e[1D", $this->getActualOutputAndClean());
    }

    public function testCursorUp()
    {
        $this->output->cursorUp(1);
        $this->assertSameWithTag("\e[1A", $this->getActualOutputAndClean());
    }

    public function testCursorDown()
    {
        $this->output->cursorDown(1);
        $this->assertSameWithTag("\e[1B", $this->getActualOutputAndClean());
    }

    public function testCursorPlace()
    {
        $this->output->cursorPlace(4, 8);
        $this->assertSameWithTag("\e[4;8H", $this->getActualOutputAndClean());
    }

    public function testCursorPlaceDisablesClearLine()
    {
        $this->output->write("stuff!");
        $this->assertTrue($this->output->isClearLineEnabled());

        $this->output->cursorPlace(1, 1);
        $this->assertFalse($this->output->isClearLineEnabled());

        // This should do nothing
        $this->output->clearLine();

        // If clear line had worked, there would be many spaces. The string we're expecting does not.
        $this->assertSame("stuff!\e[0m\e[1;1H\e[0m", $this->getActualOutputAndClean());
    }

    public function testCls()
    {
        $this->output->cls();
        $this->assertSameWithTag("\ec", $this->getActualOutputAndClean());
    }

    public function testClearLine()
    {
        $this->output->write("12345");
        $this->output->clearLine();
        $this->output->write("no");

        // Use urlencode because the carriage return escape codes are annoying to escape otherwise
        $output = urlencode($this->getActualOutputAndClean());

        // Check that we've got 2 carriage returns and then remove %0D (carriage return)
        $this->assertSame(2, substr_count($output, "%0D"));
        $output = str_replace("%0D", "", $output);

        // Straight up remove %1B (backslash) and %5B (square bracket) combinations (the reset style \[0m)
        $output = str_replace("%1B%5B0m", "", $output);

        // Check that we've got the correct amount of spaces (+)
        $spaces = str_repeat("+", $this->output->getTerminalWidth());

        $this->assertSame(
            $output,
            "12345{$spaces}no"
        );
    }

    public function testWriteErrorBlock()
    {
        $this->output->writeErrorBlock('error');

        $output = [
            $this->addTag(""),
            $this->addTag(" <error>┌───────┐</error>"),
            $this->addTag(" <error>│ error │</error>"),
            $this->addTag(" <error>└───────┘</error>"),
            $this->addTag(""),
            "",
        ];

        $this->assertSame(
            implode("\n", $output),
            $this->getActualOutputAndClean()
        );
    }

    public function testWriteInfoBlock()
    {
        $this->output->writeInfoBlock('info');

        $output = [
            $this->addTag(""),
            $this->addTag(" <info>┌──────┐</info>"),
            $this->addTag(" <info>│ info │</info>"),
            $this->addTag(" <info>└──────┘</info>"),
            $this->addTag(""),
            "",
        ];

        $this->assertSame(
            implode("\n", $output),
            $this->getActualOutputAndClean()
        );
    }

    public function testWriteSuccessBlock()
    {
        $this->output->writeSuccessBlock('success');

        $output = [
            $this->addTag(""),
            $this->addTag(" <success>┌─────────┐</success>"),
            $this->addTag(" <success>│ success │</success>"),
            $this->addTag(" <success>└─────────┘</success>"),
            $this->addTag(""),
            "",
        ];

        $this->assertSame(
            implode("\n", $output),
            $this->getActualOutputAndClean()
        );
    }

    public function testWriteBlockWithAnyTag()
    {
        $this->output->writeBlock('any block', 'anytag');

        $output = [
            $this->addTag(""),
            $this->addTag(" <anytag>┌───────────┐</anytag>"),
            $this->addTag(" <anytag>│ any block │</anytag>"),
            $this->addTag(" <anytag>└───────────┘</anytag>"),
            $this->addTag(""),
            "",
        ];

        $this->assertSame(
            implode("\n", $output),
            $this->getActualOutputAndClean()
        );
    }

    public function testWriteBlockWithTagsUsingMultipleTags()
    {
        $this->output->writeBlockWithTags('any block', ["1", "2", "3"]);

        $output = [
            $this->addTag(""),
            $this->addTag(" <1><2><3>┌───────────┐</1></2></3>"),
            $this->addTag(" <1><2><3>│ any block │</1></2></3>"),
            $this->addTag(" <1><2><3>└───────────┘</1></2></3>"),
            $this->addTag(""),
            "",
        ];

        $this->assertSame(
            implode("\n", $output),
            $this->getActualOutputAndClean()
        );
    }

    public function testWriteBlockWithTagsUsingNoTagsOutputsNoTags()
    {
        $this->output->writeBlockWithTags('any block', []);

        $output = [
            $this->addTag(""),
            $this->addTag(" ┌───────────┐"),
            $this->addTag(" │ any block │"),
            $this->addTag(" └───────────┘"),
            $this->addTag(""),
            "",
        ];

        $this->assertSame(
            implode("\n", $output),
            $this->getActualOutputAndClean()
        );
    }

    public function testParseTagsForRealThisTime()
    {
        $output = new \Parable\Console\Output();

        // Unknown tags are ignored and not replaced, but still get the defaultTag at the end to reset any styles
        $this->assertSame($this->addTag('<tag>unknown</tag>'), $output->parseTags('<tag>unknown</tag>'));

        // Since tags are escaped with the defaultTag at the end, we'll need 2
        $this->assertSame($this->addTag("\e[;32mgreen", 2), $output->parseTags('<green>green</green>'));
        $this->assertSame($this->addTag("\e[;31mred", 2), $output->parseTags('<red>red</red>'));

        // And a more complex one, with both a fore- and a background color
        // Since tags are escaped with the defaultTag at the end and there's two tags, we'll need 3
        $this->assertSame(
            $this->addTag("\e[;31m\e[47mred on lightgray", 3),
            $output->parseTags('<red><bg_light_gray>red on lightgray</bg_light_gray></red>')
        );
    }

    public function testGetTerminalWidth()
    {
        // Since Output::isInteractiveShell always returns false, assume default value
        $this->assertSame(80, $this->output->getTerminalWidth());
    }

    public function testGetTerminalHeight()
    {
        // Since Output::isInteractiveShell always returns false, assume default value
        $this->assertSame(25, $this->output->getTerminalHeight());
    }
}
