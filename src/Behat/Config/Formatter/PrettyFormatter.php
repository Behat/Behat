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

        if (null !== $timer) {
            $settings['timer'] = $timer;
        }

        if (null !== $expand) {
            $settings['expand'] = $expand;
        }

        if (null !== $paths) {
            $settings['paths'] = $paths;
        }

        if (null !== $multiline) {
            $settings['multiline'] = $multiline;
        }

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
