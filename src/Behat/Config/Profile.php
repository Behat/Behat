<?php

declare(strict_types=1);

namespace Behat\Config;

use Behat\Config\Filter\FilterInterface;
use Behat\Config\Filter\NameFilter;
use Behat\Config\Filter\NarrativeFilter;
use Behat\Config\Filter\RoleFilter;
use Behat\Config\Filter\TagFilter;
use Behat\Config\Formatter\Formatter;
use Behat\Config\Formatter\FormatterConfigInterface;
use Behat\Config\Formatter\JUnitFormatter;
use Behat\Config\Formatter\PrettyFormatter;
use Behat\Config\Formatter\ProgressFormatter;
use Behat\Config\Formatter\ShowOutputOption;
use Behat\Testwork\ServiceContainer\Exception\ConfigurationLoadingException;
use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Name\FullyQualified;

final class Profile
{
    private const SUITES_SETTING = 'suites';
    private const EXTENSIONS_SETTING = 'extensions';
    private const GHERKIN_SETTING = 'gherkin';
    private const FILTERS_SETTING = 'filters';
    private const FORMATTERS_SETTING = 'formatters';
    private const DEFINITIONS_SETTING = 'definitions';
    private const PRINT_UNUSED_DEFINITIONS_SETTING = 'print_unused_definitions';

    private const DISABLE_FORMATTER_FUNCTION = 'disableFormatter';
    private const FORMATTER_FUNCTION = 'withFormatter';
    private const FILTER_FUNCTION = 'withFilter';
    private const UNUSED_DEFINITIONS_FUNCTION = 'withPrintUnusedDefinitions';
    private const EXTENSION_FUNCTION = 'withExtension';
    private const SUITE_FUNCTION = 'withSuite';

    private BuilderFactory $builderFactory;

    public function __construct(
        private string $name,
        private array $settings = []
    ) {
        $this->builderFactory = new BuilderFactory();
    }

    public function withSuite(Suite $suite): self
    {
        if (array_key_exists($suite->name(), $this->settings[self::SUITES_SETTING] ?? [])) {
            throw new ConfigurationLoadingException(sprintf('The suite "%s" already exists.', $suite->name()));
        }

        $this->settings[self::SUITES_SETTING][$suite->name()] = $suite->toArray();

        return $this;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function withExtension(ExtensionConfigInterface $extension): self
    {
        if (array_key_exists($extension->name(), $this->settings[self::EXTENSIONS_SETTING] ?? [])) {
            throw new ConfigurationLoadingException(sprintf('The extension "%s" already exists.', $extension->name()));
        }

        $this->settings[self::EXTENSIONS_SETTING][$extension->name()] = $extension->toArray();

        return $this;
    }

    public function withFilter(FilterInterface $filter): self
    {
        if (array_key_exists($filter->name(), $this->settings[self::GHERKIN_SETTING][self::FILTERS_SETTING] ?? [])) {
            throw new ConfigurationLoadingException(sprintf('The filter "%s" already exists.', $filter->name()));
        }

        $this->settings[self::GHERKIN_SETTING][self::FILTERS_SETTING][$filter->name()] = $filter->value();

        return $this;
    }

    public function withFormatter(FormatterConfigInterface $formatter): self
    {
        $this->settings[self::FORMATTERS_SETTING][$formatter->name()] = $formatter->toArray();

        return $this;
    }

    public function disableFormatter(string $name): self
    {
        $this->settings[self::FORMATTERS_SETTING][$name] = false;

        return $this;
    }

    public function withPrintUnusedDefinitions(bool $printUnusedDefinitions = true): self
    {
        $this->settings[self::DEFINITIONS_SETTING][self::PRINT_UNUSED_DEFINITIONS_SETTING] = $printUnusedDefinitions;

        return $this;
    }

    public function toArray(): array
    {
        return $this->settings;
    }

    public function toPhpExpr(): Node\Expr
    {
        $profileObject =  $this->builderFactory->new(new FullyQualified(self::class));
        $expr = $profileObject;

        $this->addFormattersToExpr($expr);
        $this->addFiltersToExpr($expr);
        $this->addUnusedDefinitionsToExpr($expr);
        $this->addExtensionsToExpr($expr);
        $this->addSuitesToExpr($expr);

        if (count($this->settings) === 0) {
            $args = $this->builderFactory->args([$this->name]);
        } else {
            $args = $this->builderFactory->args([$this->name, $this->settings]);
        }
        $profileObject->args = $args;

        return $expr;
    }

    private function addFormattersToExpr(Expr &$expr): void
    {
        if (isset($this->settings[self::FORMATTERS_SETTING])) {
            foreach ($this->settings[self::FORMATTERS_SETTING] as $name => $formatterSettings) {
                if ($formatterSettings === false) {
                    $args = $this->builderFactory->args([$name]);
                    $expr = $this->builderFactory->methodCall($expr, self::DISABLE_FORMATTER_FUNCTION, $args);
                } else {
                    if ($formatterSettings === null) {
                        $formatterSettings = true;
                    }
                    if (isset($formatterSettings[ShowOutputOption::OPTION_NAME])) {
                        $formatterSettings[ShowOutputOption::PARAMETER_NAME] =
                            ShowOutputOption::from($formatterSettings[ShowOutputOption::OPTION_NAME]);
                        unset($formatterSettings[ShowOutputOption::OPTION_NAME]);
                    }

                    $formatter = match($name) {
                        PrettyFormatter::NAME => $formatterSettings === true ? new PrettyFormatter() : new PrettyFormatter(...$formatterSettings),
                        ProgressFormatter::NAME => $formatterSettings === true ? new ProgressFormatter() : new ProgressFormatter(...$formatterSettings),
                        JUnitFormatter::NAME => new JUnitFormatter(),
                        default => $formatterSettings === true ? new Formatter($name) : new Formatter($name, $formatterSettings),
                    };
                    $formatterExpr = $formatter->toPhpExpr();
                    $args = $this->builderFactory->args([$formatterExpr]);
                    $expr = $this->builderFactory->methodCall($expr, self::FORMATTER_FUNCTION, $args);
                }
            }
            unset($this->settings[self::FORMATTERS_SETTING]);
        }
    }

    private function addFiltersToExpr(Expr &$expr): void
    {
        if (isset($this->settings[self::GHERKIN_SETTING][self::FILTERS_SETTING])) {
            foreach ($this->settings[self::GHERKIN_SETTING][self::FILTERS_SETTING] as $name => $filterValue) {
                $filter = match($name) {
                    NameFilter::NAME => new NameFilter($filterValue),
                    NarrativeFilter::NAME => new NarrativeFilter($filterValue),
                    RoleFilter::NAME => new RoleFilter($filterValue),
                    TagFilter::NAME => new TagFilter($filterValue),
                    default => null,
                };
                if ($filter !== null) {
                    $args = $this->builderFactory->args([$filter->toPhpExpr()]);
                    $expr = $this->builderFactory->methodCall($expr, self::FILTER_FUNCTION, $args);
                    unset($this->settings[self::GHERKIN_SETTING][self::FILTERS_SETTING][$name]);
                }
            }
            if ($this->settings[self::GHERKIN_SETTING][self::FILTERS_SETTING] === []) {
                unset($this->settings[self::GHERKIN_SETTING][self::FILTERS_SETTING]);
                if ($this->settings[self::GHERKIN_SETTING] === []) {
                    unset($this->settings[self::GHERKIN_SETTING]);
                }
            }
        }
    }

    private function addUnusedDefinitionsToExpr(Expr &$expr): void
    {
        if (isset($this->settings[self::DEFINITIONS_SETTING][self::PRINT_UNUSED_DEFINITIONS_SETTING])) {
            $args = $this->builderFactory->args([
                $this->settings[self::DEFINITIONS_SETTING][self::PRINT_UNUSED_DEFINITIONS_SETTING]
            ]);
            $expr = $this->builderFactory->methodCall($expr, self::UNUSED_DEFINITIONS_FUNCTION, $args);
            unset($this->settings[self::DEFINITIONS_SETTING][self::PRINT_UNUSED_DEFINITIONS_SETTING]);
            if ($this->settings[self::DEFINITIONS_SETTING] === []) {
                unset($this->settings[self::DEFINITIONS_SETTING]);
            }
        }
    }

    private function addExtensionsToExpr(Expr &$expr): void
    {
        if (isset($this->settings[self::EXTENSIONS_SETTING])) {
            foreach ($this->settings[self::EXTENSIONS_SETTING] as $name => $extensionSettings) {
                $extensionObject = new Extension($name, $extensionSettings ?? []);
                $args = $this->builderFactory->args([$extensionObject->toPhpExpr()]);
                $expr = $this->builderFactory->methodCall($expr, self::EXTENSION_FUNCTION, $args);
                unset($this->settings[self::EXTENSIONS_SETTING][$name]);
            }
            if ($this->settings[self::EXTENSIONS_SETTING] === []) {
                unset($this->settings[self::EXTENSIONS_SETTING]);
            }
        }
    }

    private function addSuitesToExpr(Expr &$expr): void
    {
        if (isset($this->settings[self::SUITES_SETTING])) {
            foreach ($this->settings[self::SUITES_SETTING] as $name => $suiteSettings) {
                $suiteObject = new Suite($name, $suiteSettings ?? []);
                $args = $this->builderFactory->args([$suiteObject->toPhpExpr()]);
                $expr = $this->builderFactory->methodCall($expr, self::SUITE_FUNCTION, $args);
                unset($this->settings[self::SUITES_SETTING][$name]);
            }
            unset($this->settings[self::SUITES_SETTING]);
        }
    }
}
