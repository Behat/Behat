<?php

declare(strict_types=1);

namespace Behat\Config\Formatter;

final class ProgressFormatter extends Formatter
{
    public const NAME = 'progress';

    /**
     * @param bool $timer show time and memory usage at the end of the test run
     * @param string $showOutput show the test stdout output as part of the formatter output (yes, no, on-fail, in-summary)
     */
    public function __construct(
        bool $timer = true,
        string $showOutput = 'in-summary',
    ) {
        parent::__construct(name: self::NAME, settings: [
            'timer' => $timer,
            'show_output' => $showOutput
        ]);
    }

    public static function defaults(): array
    {
        return (new self())->toArray();
    }
}
