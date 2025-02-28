<?php

declare(strict_types=1);

namespace Behat\Config\Formatter;

use Behat\Config\ConfigConverterInterface;
use PhpParser\BuilderFactory;
use PhpParser\Node\Expr;
use PhpParser\Node\Name\FullyQualified;

class Formatter implements FormatterConfigInterface, ConfigConverterInterface
{
    private const OUTPUT_VERBOSITY_SETTING = 'output_verbosity';
    private const OUTPUT_PATH_SETTING = 'output_path';
    private const OUTPUT_DECORATE_SETTING = 'output_decorate';
    private const OUTPUT_STYLES_SETTING = 'output_styles';

    private const OUTPUT_VERBOSITY_FUNCTION = 'withOutputVerbosity';
    private const OUTPUT_PATH_FUNCTION = 'withOutputPath';
    private const OUTPUT_DECORATE_FUNCTION = 'withOutputDecorated';
    private const OUTPUT_STYLES_FUNCTION = 'withOutputStyles';

    private const FORMATTER_FUNCTION_NAMES_PER_SETTING = [
        self::OUTPUT_VERBOSITY_SETTING => self::OUTPUT_VERBOSITY_FUNCTION,
        self::OUTPUT_PATH_SETTING => self::OUTPUT_PATH_FUNCTION,
        self::OUTPUT_DECORATE_SETTING => self::OUTPUT_DECORATE_FUNCTION,
        self::OUTPUT_STYLES_SETTING => self::OUTPUT_STYLES_FUNCTION,
    ];

    private BuilderFactory $builderFactory;

    public function __construct(
        private readonly string $name,
        private array $settings = [],
    ) {
        $this->builderFactory = new BuilderFactory();
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

    public function toPhpExpr(): Expr
    {
        $formatterObject =  $this->builderFactory->new(new FullyQualified(self::class));

        $expr = $this->applyBaseSettings($formatterObject);

        if ($this->settings === []) {
            $args = $this->builderFactory->args([$this->name]);
        } else {
            $args = $this->builderFactory->args([$this->name, $this->settings]);
        }
        $formatterObject->args = $args;

        return $expr;
    }

    public static function defaults(): array
    {
        return [];
    }

    public function applyBaseSettings(Expr $expr): Expr
    {
        foreach ($this->settings as $settingName => $setting) {
            $functionName = Formatter::FORMATTER_FUNCTION_NAMES_PER_SETTING[$settingName] ?? null;
            if ($functionName !== null) {
                $args = $this->builderFactory->args([$setting]);
                $expr = $this->builderFactory->methodCall($expr, $functionName, $args);
                unset($this->settings[$settingName]);
            }
        }
        return $expr;
    }

    protected function toPhpExprForNamedFormatter(): Expr
    {
        $formatterObject =  $this->builderFactory->new(new FullyQualified(static::class));

        $expr = $this->applyBaseSettings($formatterObject);

        $defaults = static::defaults();
        $argValues = [];
        foreach ($defaults as $name => $defaultValue) {
            if ($this->settings[$name] !== $defaultValue) {
                $value = $this->settings[$name];
                if ($name === ShowOutputOption::OPTION_NAME) {
                    $value = ShowOutputOption::from($value);
                    $name = ShowOutputOption::PARAMETER_NAME;
                }
                $argValues[$name] = $value;
            }
        }
        if ($argValues !== []) {
            $args = $this->builderFactory->args($argValues);
            $formatterObject->args = $args;
        }

        return $expr;
    }
}
