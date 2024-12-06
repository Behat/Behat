<?php

declare(strict_types=1);

namespace Behat\Config\Formatter;

final class PrettyFormatter extends Formatter
{
    public const NAME = 'pretty';

    public function __construct(
        ?bool $timer = null,
        ?bool $expand = null,
        ?bool $paths = null,
        ?bool $multiline = null,
    ) {
        $settings = self::defaults();

        $settings['timer'] = $timer ?? $settings['timer'];
        $settings['expand'] = $expand ?? $settings['expand'];
        $settings['paths'] = $paths ?? $settings['paths'];
        $settings['multiline'] = $multiline ?? $settings['multiline'];

        parent::__construct(name: self::NAME, settings: $settings);
    }

    public static function defaults(): array
    {
        return [
            'timer' => true,
            'expand' => false,
            'paths' => true,
            'multiline' => true,
        ];
    }
}
