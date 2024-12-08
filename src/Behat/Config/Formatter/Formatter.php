<?php

declare(strict_types=1);

namespace Behat\Config\Formatter;

use Behat\Testwork\Output\Printer\Factory\OutputFactory;

class Formatter implements FormatterConfigInterface
{
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
        $this->settings['output_verbosity'] = $level;

        return $this;
    }

    public function withOutputPath(string $path): self
    {
        $this->settings['output_path'] = $path;

        return $this;
    }

    public function withOutputDecorated(bool $decorated = true): self
    {
        $this->settings['output_decorate'] = $decorated;

        return $this;
    }

    public function withOutputStyles(array $styles): self
    {
        $this->settings['output_styles'] = $styles;

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
