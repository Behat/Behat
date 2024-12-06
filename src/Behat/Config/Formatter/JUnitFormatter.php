<?php

declare(strict_types=1);

namespace Behat\Config\Formatter;

final class JUnitFormatter extends Formatter
{
    public function __construct(
        bool $timer = true,
    ) {
        parent::__construct(name: 'junit', settings: [
            'timer' => $timer,
        ]);
    }
}
