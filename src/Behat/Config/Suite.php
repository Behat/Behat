<?php

declare(strict_types=1);

namespace Behat\Config;

use Behat\Config\Filter\FilterInterface;
use Behat\Config\Filter\NameFilter;
use Behat\Config\Filter\NarrativeFilter;
use Behat\Config\Filter\RoleFilter;
use Behat\Config\Filter\TagFilter;
use Behat\Testwork\ServiceContainer\Exception\ConfigurationLoadingException;
use PhpParser\BuilderFactory;
use PhpParser\Node\Expr;
use PhpParser\Node\Name\FullyQualified;

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
    public function toPhpExpr(BuilderFactory $builderFactory): Expr
    {
        $suiteObject =  $builderFactory->new(new FullyQualified(self::class));

        $expr = $suiteObject;

        $this->addContextsToExpr($expr, $builderFactory);
        $this->addPathsToExpr($expr, $builderFactory);
        $this->addFiltersToExpr($expr, $builderFactory);

        if ($this->settings === []) {
            $args = $builderFactory->args([$this->name]);
        } else {
            $args = $builderFactory->args([$this->name, $this->settings]);
        }
        $suiteObject->args = $args;

        return $expr;
    }

    private function addContextsToExpr(Expr &$expr, BuilderFactory $builderFactory): void
    {
        if (isset($this->settings[self::CONTEXTS_SETTING])) {
            $args = $builderFactory->args($this->settings[self::CONTEXTS_SETTING]);
            $expr = $builderFactory->methodCall($expr, self::CONTEXTS_FUNCTION, $args);
            unset($this->settings[self::CONTEXTS_SETTING]);
        }
    }

    private function addPathsToExpr(Expr &$expr, BuilderFactory $builderFactory): void
    {
        if (isset($this->settings[self::PATHS_SETTING])) {
            $args = $builderFactory->args($this->settings[self::PATHS_SETTING]);
            $expr = $builderFactory->methodCall($expr, self::PATHS_FUNCTION, $args);
            unset($this->settings[self::PATHS_SETTING]);
        }
    }

    private function addFiltersToExpr(Expr &$expr, BuilderFactory $builderFactory): void
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
                    $args = $builderFactory->args([$filter->toPhpExpr($builderFactory)]);
                    $expr = $builderFactory->methodCall($expr, self::FILTER_FUNCTION, $args);
                    unset($this->settings[self::FILTERS_SETTING][$filterName]);
                }
            }
            if ($this->settings[self::FILTERS_SETTING] === []) {
                unset($this->settings[self::FILTERS_SETTING]);
            }
        }
    }
}
