<?php

declare(strict_types=1);

namespace Behat\Config\Formatter;

final class ProgressFormatter extends Formatter
{
    public const NAME = 'progress';

    /**
     * @param bool $timer show time and memory usage at the end of the test run
     * @param ShowOutputOption $showOutput show the test stdout output as part of the
     *                                     formatter output (yes, no, on-fail, in-summary)
     */
    public function __construct(
        bool             $timer = true,
        ShowOutputOption $showOutput = ShowOutputOption::InSummary,
    ) {
        parent::__construct(name: self::NAME, settings: [
            'timer' => $timer,
            ShowOutputOption::OPTION_NAME => $showOutput->value
        ]);
    }

    public static function defaults(): array
    {
        return (new self())->toArray();
    }
}
