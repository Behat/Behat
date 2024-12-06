<?php

declare(strict_types=1);

namespace Behat\Config\Formatter;

final class ProgressFormatter extends Formatter
{
    public const NAME = 'progress';

    public function __construct(
        bool $timer = true,
    ) {
        parent::__construct(name: self::NAME, settings: [
            'timer' => $timer,
        ]);
    }
}
