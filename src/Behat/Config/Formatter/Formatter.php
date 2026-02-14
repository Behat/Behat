<?php

declare(strict_types=1);

namespace Behat\Config\Formatter;

class Formatter implements FormatterConfigInterface
{
    private const OUTPUT_VERBOSITY_SETTING = 'output_verbosity';
    private const OUTPUT_PATH_SETTING = 'output_path';
    private const OUTPUT_DECORATE_SETTING = 'output_decorate';
    private const OUTPUT_STYLES_SETTING = 'output_styles';

    public function __construct(
        private readonly string $name,
        private array $settings = [],
    ) {
    }

    /**
     * @param int $level use OutputFactory::VERBOSITY_*
     */
    public function withOutputVerbosity(int $level): self
    {
        $this->settings[self::OUTPUT_VERBOSITY_SETTING] = $level;

        return $this;
    }

    public function withOutputPath(string $path): self
    {
        $this->settings[self::OUTPUT_PATH_SETTING] = $path;

        return $this;
    }

    public function withOutputDecorated(bool $decorated = true): self
    {
        $this->settings[self::OUTPUT_DECORATE_SETTING] = $decorated;

        return $this;
    }

    public function withOutputStyles(array $styles): self
    {
        $this->settings[self::OUTPUT_STYLES_SETTING] = $styles;

        return $this;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function toArray(): array
    {
        return $this->settings;
    }

    public static function defaults(): array
    {
        return [];
    }
}
