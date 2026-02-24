<?php

namespace Behat\Config;

use Behat\Config\Converter\ConfigConverterTools;
use Behat\Config\Filter\FilterInterface;
use Behat\Config\Filter\NameFilter;
use Behat\Config\Filter\NarrativeFilter;
use Behat\Config\Filter\RoleFilter;
use Behat\Config\Filter\TagFilter;
use Behat\Gherkin\GherkinCompatibilityMode;
use Behat\Testwork\ServiceContainer\Exception\ConfigurationLoadingException;
use PhpParser\Node\Expr;

final class GherkinOptions implements ConfigConverterInterface
{
    private const CACHE_SETTING = 'cache';
    private const COMPATIBILITY_SETTING = 'compatibility';
    private const FILTERS_SETTING = 'filters';

    private const CACHE_FUNCTION = 'withCacheDir';
    private const COMPATIBILITY_FUNCTION = 'withCompatibilityMode';
    private const FILTER_FUNCTION = 'withFilter';

    public function __construct(
        private array $settings = [],
    ) {
    }

    public function toArray(): array
    {
        return $this->settings;
    }

    /**
     * Sets the parser cache directory (defaults to the system tmp dir, if writable).
     */
    public function withCacheDir(string $dir): self
    {
        $this->settings[self::CACHE_SETTING] = $dir;

        return $this;
    }

    /**
     * Controls the extent to which gherkin is parsed equivalent to other cucumber tools.
     *
     * In legacy mode (the default), feature files are parsed as they have been in previous versions of Behat. This
     * differs slightly from the behaviour of current versions of the official cucumber parsers and runners.
     *
     * Other modes will parse identical to the official cucumber parsers.
     */
    public function withCompatibilityMode(GherkinCompatibilityMode $mode): self
    {
        $this->settings[self::COMPATIBILITY_SETTING] = $mode->value;

        return $this;
    }

    public function withFilter(FilterInterface $filter): self
    {
        if (array_key_exists($filter->name(), $this->settings[self::FILTERS_SETTING] ?? [])) {
            throw new ConfigurationLoadingException(sprintf('The filter "%s" already exists.', $filter->name()));
        }

        $this->settings[self::FILTERS_SETTING][$filter->name()] = $filter->value();

        return $this;
    }

    /**
     * @internal
     */
    public function toPhpExpr(): Expr
    {
        $optionsObject = ConfigConverterTools::createObject(self::class);
        $expr = $optionsObject;

        $this->addCacheToExpr($expr);
        $this->addCompatibilityModeToExpr($expr);
        $this->addFiltersToExpr($expr);

        $arguments = count($this->settings) === 0 ? [] : [$this->settings];
        ConfigConverterTools::addArgumentsToConstructor($arguments, $optionsObject);

        return $expr;
    }

    private function addCacheToExpr(Expr &$expr): void
    {
        if (isset($this->settings[self::CACHE_SETTING])) {
            $expr = ConfigConverterTools::addMethodCall(
                self::class,
                self::CACHE_FUNCTION,
                [$this->settings[self::CACHE_SETTING]],
                $expr
            );
            unset($this->settings[self::CACHE_SETTING]);
        }
    }

    private function addCompatibilityModeToExpr(Expr &$expr): void
    {
        if (isset($this->settings[self::COMPATIBILITY_SETTING])) {
            $expr = ConfigConverterTools::addMethodCall(
                self::class,
                self::COMPATIBILITY_FUNCTION,
                [GherkinCompatibilityMode::from($this->settings[self::COMPATIBILITY_SETTING])],
                $expr
            );
            unset($this->settings[self::COMPATIBILITY_SETTING]);
        }
    }

    private function addFiltersToExpr(Expr &$expr): void
    {
        if (!isset($this->settings[self::FILTERS_SETTING])) {
            return;
        }

        foreach ($this->settings[self::FILTERS_SETTING] as $name => $filterValue) {
            $filter = match ($name) {
                NameFilter::NAME => new NameFilter($filterValue),
                NarrativeFilter::NAME => new NarrativeFilter($filterValue),
                RoleFilter::NAME => new RoleFilter($filterValue),
                TagFilter::NAME => new TagFilter($filterValue),
                default => null,
            };
            if ($filter !== null) {
                $expr = ConfigConverterTools::addMethodCall(
                    self::class,
                    self::FILTER_FUNCTION,
                    [$filter->toPhpExpr()],
                    $expr
                );
                unset($this->settings[self::FILTERS_SETTING][$name]);
            }
        }
        if ($this->settings[self::FILTERS_SETTING] === []) {
            unset($this->settings[self::FILTERS_SETTING]);
        }
    }
}
