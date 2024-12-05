<?php

declare(strict_types=1);

namespace Behat\Config\Formatter;

final class ProgressFormatter extends Formatter
{
    public const NAME = 'progress';

    /**
     * @param bool $timer show time and memory usage at the end of the test run
     */
    public function __construct(
        bool $timer = true,
    ) {
        parent::__construct(name: self::NAME, settings: [
            'timer' => $timer,
        ]);
    }

    public static function defaults(): array
    {
        return (new self())->toArray();
    }
}
