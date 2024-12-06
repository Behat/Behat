<?php

declare(strict_types=1);

namespace Behat\Config\Formatter;

final class PrettyFormatter extends Formatter
{
    public function __construct(
        bool $timer = true,
        bool $expand = false,
        bool $paths = true,
        bool $multiline = true,
    ) {
        parent::__construct(name: 'pretty', settings: [
            'timer' => $timer,
            'expand' => $expand,
            'paths' => $paths,
            'multiline' => $multiline,
        ]);
    }
}
