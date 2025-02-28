<?php

declare(strict_types=1);

namespace Behat\Config\Formatter;

use PhpParser\Node\Expr;
use UnexpectedValueException;

final class PrettyFormatter extends Formatter
{
    public const NAME = 'pretty';

    private const TIMER_SETTING = 'timer';
    private const EXPAND_SETTING = 'expand';
    private const PATHS_SETTING = 'paths';
    private const MULTILINE_SETTING = 'multiline';

    /**
     * @param bool $timer show time and memory usage at the end of the test run
     * @param bool $expand print each example of a scenario outline separately
     * @param bool $paths display the file path and line number for each scenario
     *                    and the context file and method for each step
     * @param bool $multiline print out PyStrings and TableNodes in full
     * @param ShowOutputOption $showOutput show the test stdout output as part of the
     *                                     formatter output (yes, no, on-fail)
     */
    public function __construct(
        bool             $timer = true,
        bool             $expand = false,
        bool             $paths = true,
        bool             $multiline = true,
        ShowOutputOption $showOutput = ShowOutputOption::Yes,
        ...$baseOptions
    ) {
        if ($showOutput === ShowOutputOption::InSummary) {
            throw new UnexpectedValueException(
                'The pretty formatter does not support the "in-summary" show output option'
            );
        }
        $settings = [
            self::TIMER_SETTING => $timer,
            self::EXPAND_SETTING => $expand,
            self::PATHS_SETTING => $paths,
            self::MULTILINE_SETTING => $multiline,
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
