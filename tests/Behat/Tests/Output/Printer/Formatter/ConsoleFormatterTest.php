<?php

namespace Behat\Tests\Output\Printer\Formatter;

use Behat\Behat\Output\Printer\Formatter\ConsoleFormatter;
use PHPUnit\Framework\TestCase;

class ConsoleFormatterTest extends TestCase
{
    public function testFormatValidMessageWithoutDecoration(): void
    {
        $consoleFormatter = new ConsoleFormatter();

        $formattedText = $consoleFormatter->format('{+info}Info:{-info}');

        $this->assertEquals('Info:', $formattedText);
    }

    public function testFormatValidMessageWithDecoration(): void
    {
        $consoleFormatter = new ConsoleFormatter(true);

        $formattedText = $consoleFormatter->format('{+info}Info:{-info}');

        $this->assertEquals('[32mInfo:[39m', $formattedText);
    }

    public function testFormatInvalidMessage(): void
    {
        $consoleFormatter = new ConsoleFormatter(true);

        $originalBacktrackLimit = ini_set('pcre.backtrack_limit', '100');
        try {
            $formattedText = $consoleFormatter->format('{+info}'.str_repeat('a', 1000).'{-info}');
        } finally {
            ini_set('pcre.backtrack_limit', $originalBacktrackLimit);
        }

        $this->assertEquals('Error formatting output: Regex failed: Backtrack limit exhausted', $formattedText);
    }
}
