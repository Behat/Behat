<?php

declare(strict_types=1);

namespace Behat\Config;

use Behat\Config\Converter\ConfigConverterTools;
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
use PhpParser\Node;
use PhpParser\Node\Expr;

final class Profile implements ConfigConverterInterface
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

    public function __construct(
        private string $name,
        private array $settings = [],
    ) {
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

    /**
     * @internal
     */
    public function toPhpExpr(): Node\Expr
    {
        $profileObject = ConfigConverterTools::createObject(self::class);
        $expr = $profileObject;

        $this->addFormattersToExpr($expr);
        $this->addFiltersToExpr($expr);
        $this->addUnusedDefinitionsToExpr($expr);
        $this->addExtensionsToExpr($expr);
        $this->addSuitesToExpr($expr);

        if (count($this->settings) === 0) {
            $arguments = [$this->name];
        } else {
            $arguments = [$this->name, $this->settings];
        }
        ConfigConverterTools::addArgumentsToConstructor($arguments, $profileObject);

        return $expr;
    }

    private function addFormattersToExpr(Expr &$expr): void
    {
        if (!isset($this->settings[self::FORMATTERS_SETTING])) {
            return;
        }
        foreach ($this->settings[self::FORMATTERS_SETTING] as $name => $formatterSettings) {
            if ($formatterSettings === false) {
                $expr = ConfigConverterTools::addMethodCall(
                    self::DISABLE_FORMATTER_FUNCTION,
                    [$name],
                    $expr
                );
            } else {
                if ($formatterSettings === null) {
                    $formatterSettings = true;
                }
                if (isset($formatterSettings[ShowOutputOption::OPTION_NAME])) {
                    $formatterSettings[ShowOutputOption::PARAMETER_NAME] =
                        ShowOutputOption::from($formatterSettings[ShowOutputOption::OPTION_NAME]);
                    unset($formatterSettings[ShowOutputOption::OPTION_NAME]);
                }

                $formatter = match ($name) {
                    PrettyFormatter::NAME => $formatterSettings === true ? new PrettyFormatter() : new PrettyFormatter(...$formatterSettings),
                    ProgressFormatter::NAME => $formatterSettings === true ? new ProgressFormatter() : new ProgressFormatter(...$formatterSettings),
                    JUnitFormatter::NAME => new JUnitFormatter(),
                    default => $formatterSettings === true ? new Formatter($name) : new Formatter($name, $formatterSettings),
                };
                $expr = ConfigConverterTools::addMethodCall(
                    self::FORMATTER_FUNCTION,
                    [$formatter->toPhpExpr()],
                    $expr
                );
            }
        }
        unset($this->settings[self::FORMATTERS_SETTING]);
    }

    private function addFiltersToExpr(Expr &$expr): void
    {
        if (!isset($this->settings[self::GHERKIN_SETTING][self::FILTERS_SETTING])) {
            return;
        }
        foreach ($this->settings[self::GHERKIN_SETTING][self::FILTERS_SETTING] as $name => $filterValue) {
            $filter = match ($name) {
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

    private function addUnusedDefinitionsToExpr(Expr &$expr): void
    {
        if (!isset($this->settings[self::DEFINITIONS_SETTING][self::PRINT_UNUSED_DEFINITIONS_SETTING])) {
            return;
        }
        $expr = ConfigConverterTools::addMethodCall(
            self::UNUSED_DEFINITIONS_FUNCTION,
            [$this->settings[self::DEFINITIONS_SETTING][self::PRINT_UNUSED_DEFINITIONS_SETTING]],
            $expr
        );
        unset($this->settings[self::DEFINITIONS_SETTING][self::PRINT_UNUSED_DEFINITIONS_SETTING]);
        if ($this->settings[self::DEFINITIONS_SETTING] === []) {
            unset($this->settings[self::DEFINITIONS_SETTING]);
        }
    }

    private function addExtensionsToExpr(Expr &$expr): void
    {
        if (!isset($this->settings[self::EXTENSIONS_SETTING])) {
            return;
        }
        foreach ($this->settings[self::EXTENSIONS_SETTING] as $name => $extensionSettings) {
            $extensionObject = new Extension($name, $extensionSettings ?? []);

            $expr = ConfigConverterTools::addMethodCall(
                self::EXTENSION_FUNCTION,
                [$extensionObject->toPhpExpr()],
                $expr
            );
        }
        unset($this->settings[self::EXTENSIONS_SETTING]);
    }

    private function addSuitesToExpr(Expr &$expr): void
    {
        if (!isset($this->settings[self::SUITES_SETTING])) {
            return;
        }
        foreach ($this->settings[self::SUITES_SETTING] as $name => $suiteSettings) {
            $suiteObject = new Suite($name, $suiteSettings ?? []);
            $expr = ConfigConverterTools::addMethodCall(
                self::SUITE_FUNCTION,
                [$suiteObject->toPhpExpr()],
                $expr
            );
        }
        unset($this->settings[self::SUITES_SETTING]);
    }
}
