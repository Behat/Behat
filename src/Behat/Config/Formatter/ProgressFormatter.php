<?php

declare(strict_types=1);

namespace Behat\Config\Formatter;

final class ProgressFormatter extends Formatter
{
    public const NAME = 'progress';

    public function __construct(
        ?bool $timer = null,
    ) {
        $settings = self::defaults();

        if (null !== $timer) {
            $settings['timer'] = $timer;
        }

        parent::__construct(name: self::NAME, settings: $settings);
    }

    public static function defaults(): array
    {
        return ['timer' => true];
    }
}
