<?php

declare(strict_types=1);

namespace Behat\Config\Formatter;

final class ProgressFormatter extends Formatter
{
    public const NAME = 'progress';

    private const TIMER_SETTING = 'timer';
    private const SHORT_SUMMARY_SETTING = 'short_summary';

    /**
     * @param bool $timer show time and memory usage at the end of the test run
     * @param ShowOutputOption $showOutput show the test stdout output as part of the
     *                                     formatter output (yes, no, on-fail, in-summary)
     * @param bool $shortSummary if we should print the short summary which just lists scenarios
     *                            or the long summary which lists steps
     */
    public function __construct(
        bool $timer = true,
        ShowOutputOption $showOutput = ShowOutputOption::InSummary,
        bool $shortSummary = false,
        ...$baseOptions,
    ) {
        $settings = [
            self::TIMER_SETTING => $timer,
            ShowOutputOption::OPTION_NAME => $showOutput->value,
            self::SHORT_SUMMARY_SETTING => $shortSummary,
        ];
        $settings = [...$settings, ...$baseOptions];
        parent::__construct(name: self::NAME, settings: $settings);
    }

    public static function defaults(): array
    {
        return (new self())->toArray();
    }
}
