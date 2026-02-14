<?php

declare(strict_types=1);

namespace Behat\Config\Formatter;

final class JUnitFormatter extends Formatter
{
    public const NAME = 'junit';

    private const TIMER_SETTING = 'timer';

    /**
     * @param bool $timer include run time attributes in generated report
     */
    public function __construct(
        bool $timer = true,
        ...$baseOptions,
    ) {
        $settings = [
            self::TIMER_SETTING => $timer,
        ];

        $settings = [...$settings, ...$baseOptions];

        parent::__construct(name: self::NAME, settings: $settings);
    }

    public static function defaults(): array
    {
        return (new self())->toArray();
    }
}
