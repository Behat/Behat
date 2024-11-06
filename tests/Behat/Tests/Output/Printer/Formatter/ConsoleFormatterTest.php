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

        $original_backtrack_limit = ini_get('pcre.backtrack_limit');

        ini_set('pcre.backtrack_limit', '100');

        $formattedText = $consoleFormatter->format('{+info}' . str_repeat('a', 1000) . '{-info}');

        ini_set('pcre.backtrack_limit', $original_backtrack_limit);

        $this->assertEquals('Error formatting output: Backtrack limit exhausted', $formattedText);
    }
}
