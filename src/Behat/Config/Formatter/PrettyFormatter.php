<?php

declare(strict_types=1);

namespace Behat\Config\Formatter;

final class PrettyFormatter extends Formatter
{
    public const NAME = 'pretty';

    /**
     * @param bool $timer show time and memory usage at the end of the test run
     * @param bool $expand print each example of a scenario outline separately
     * @param bool $paths display the file path and line number for each scenario
     *                    and the context file and method for each step
     * @param bool $multiline print out PyStrings and TableNodes in full
     * @param string $showOutput show the test stdout output as part of the formatter output (yes, no, on-fail)
     */
    public function __construct(
        bool $timer = true,
        bool $expand = false,
        bool $paths = true,
        bool $multiline = true,
        string $showOutput = 'yes',
    ) {
        parent::__construct(name: self::NAME, settings: [
            'timer' => $timer,
            'expand' => $expand,
            'paths' => $paths,
            'multiline' => $multiline,
            'show_output' => $showOutput
        ]);
    }

    public static function defaults(): array
    {
        return (new self())->toArray();
    }
}
