<?php

declare(strict_types=1);

namespace Behat\Config\Formatter;

use PhpParser\Node\Expr;

final class ProgressFormatter extends Formatter
{
    public const NAME = 'progress';

    private const TIMER_SETTING = 'timer';

    /**
     * @param bool $timer show time and memory usage at the end of the test run
     * @param ShowOutputOption $showOutput show the test stdout output as part of the
     *                                     formatter output (yes, no, on-fail, in-summary)
     */
    public function __construct(
        bool             $timer = true,
        ShowOutputOption $showOutput = ShowOutputOption::InSummary,
        ...$baseOptions
    ) {
        $settings = [
            self::TIMER_SETTING => $timer,
            ShowOutputOption::OPTION_NAME => $showOutput->value
        ];
        $settings = [...$settings, ...$baseOptions];
        parent::__construct(name: self::NAME, settings: $settings);
    }

    public static function defaults(): array
    {
        return (new self())->toArray();
    }

    public function toPhpExpr(): Expr
    {
        return $this->toPhpExprForNamedFormatter();
    }
}
