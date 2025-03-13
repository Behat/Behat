<?php

declare(strict_types=1);

namespace Behat\Config;

use Behat\Config\Converter\ConfigConverterTools;
use Behat\Config\Filter\FilterInterface;
use Behat\Config\Filter\NameFilter;
use Behat\Config\Filter\NarrativeFilter;
use Behat\Config\Filter\RoleFilter;
use Behat\Config\Filter\TagFilter;
use Behat\Testwork\ServiceContainer\Exception\ConfigurationLoadingException;
use PhpParser\Node\Expr;

final class Suite implements ConfigConverterInterface
{
    private const CONTEXTS_SETTING = 'contexts';
    private const PATHS_SETTING = 'paths';
    private const FILTERS_SETTING = 'filters';

    private const CONTEXTS_FUNCTION = 'withContexts';
    private const PATHS_FUNCTION = 'withPaths';
    private const FILTER_FUNCTION = 'withFilter';

    public function __construct(
        private string $name,
        private array $settings = [],
    ) {
    }

    public function withContexts(string ...$contexts): self
    {
        foreach ($contexts as $context) {
            $this->settings[self::CONTEXTS_SETTING][] = $context;
        }

        return $this;
    }

    /**
     * @param array<mixed> $constructorArgs
     */

    public function addContext(string $context, array $constructorArgs = []): self
    {
        $this->settings[self::CONTEXTS_SETTING][][$context] = $constructorArgs;

        return $this;
    }

    public function withPaths(string ...$paths): self
    {
        foreach ($paths as $path) {
            $this->settings[self::PATHS_SETTING][] = $path;
        }

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

    public function name(): string
    {
        return $this->name;
    }

    public function toArray(): array
    {
        return $this->settings;
    }

    /**
     * @internal
     */
    public function toPhpExpr(): Expr
    {
        $suiteObject = ConfigConverterTools::createObject(self::class);

        $expr = $suiteObject;

        $this->addContextsToExpr($expr);
        $this->addPathsToExpr($expr);
        $this->addFiltersToExpr($expr);

        if ($this->settings === []) {
            $arguments = [$this->name];
        } else {
            $arguments = [$this->name, $this->settings];
        }
        ConfigConverterTools::addArgumentsToConstructor($arguments, $suiteObject);

        return $expr;
    }

    private function addContextsToExpr(Expr &$expr): void
    {
        if (isset($this->settings[self::CONTEXTS_SETTING])) {
            $expr = ConfigConverterTools::addMethodCall(
                self::CONTEXTS_FUNCTION,
                $this->settings[self::CONTEXTS_SETTING],
                $expr
            );
            unset($this->settings[self::CONTEXTS_SETTING]);
        }
    }

    private function addPathsToExpr(Expr &$expr): void
    {
        if (isset($this->settings[self::PATHS_SETTING])) {
            $expr = ConfigConverterTools::addMethodCall(
                self::PATHS_FUNCTION,
                $this->settings[self::PATHS_SETTING],
                $expr
            );
            unset($this->settings[self::PATHS_SETTING]);
        }
    }

    private function addFiltersToExpr(Expr &$expr): void
    {
        if (isset($this->settings[self::FILTERS_SETTING])) {
            foreach ($this->settings[self::FILTERS_SETTING] as $filterName => $filterValue) {
                $filter = match ($filterName) {
                    NameFilter::NAME => new NameFilter($filterValue),
                    NarrativeFilter::NAME => new NarrativeFilter($filterValue),
                    RoleFilter::NAME => new RoleFilter($filterValue),
                    TagFilter::NAME => new TagFilter($filterValue),
                    default => null,
                };
                if ($filter !== null) {
                    $expr = ConfigConverterTools::addMethodCall(
                        self::FILTER_FUNCTION,
                        [$filter->toPhpExpr()],
                        $expr
                    );
                    unset($this->settings[self::FILTERS_SETTING][$filterName]);
                }
            }
            if ($this->settings[self::FILTERS_SETTING] === []) {
                unset($this->settings[self::FILTERS_SETTING]);
            }
        }
    }
}
