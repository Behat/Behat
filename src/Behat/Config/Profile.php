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
    private const PATH_OPTIONS_SETTING = 'path_options';
    private const PRINT_ABSOLUTE_PATHS_SETTING = 'print_absolute_paths';
    private const EDITOR_URL_SETTING = 'editor_url';
    private const REMOVE_PREFIX_SETTING = 'remove_prefix';

    private const DISABLE_FORMATTER_FUNCTION = 'disableFormatter';
    private const FORMATTER_FUNCTION = 'withFormatter';
    private const FILTER_FUNCTION = 'withFilter';
    private const UNUSED_DEFINITIONS_FUNCTION = 'withPrintUnusedDefinitions';
    private const EXTENSION_FUNCTION = 'withExtension';
    private const SUITE_FUNCTION = 'withSuite';
    private const PATH_OPTIONS_FUNCTION = 'withPathOptions';
    private const TESTER_OPTIONS_FUNCTION = 'withTesterOptions';

    private const PRINT_ABSOLUTE_PATHS_PARAMETER = 'printAbsolutePaths';
    private const EDITOR_URL_PARAMETER = 'editorUrl';
    private const REMOVE_PREFIX_PARAMETER = 'removePrefix';

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

    /**
     * @param string[] $removePrefix
     */
    public function withPathOptions(
        bool $printAbsolutePaths = false,
        ?string $editorUrl = null,
        array $removePrefix = [],
    ): self {
        $this->settings[self::PATH_OPTIONS_SETTING][self::PRINT_ABSOLUTE_PATHS_SETTING] = $printAbsolutePaths;

        if ($editorUrl !== null) {
            $this->settings[self::PATH_OPTIONS_SETTING][self::EDITOR_URL_SETTING] = $editorUrl;
        }

        if (!empty($removePrefix)) {
            $this->settings[self::PATH_OPTIONS_SETTING][self::REMOVE_PREFIX_SETTING] = $removePrefix;
        }

        return $this;
    }

    public function withTesterOptions(TesterOptions $options): self
    {
        // Tester options are split between multiple settings keys due to implementation details in Behat
        foreach ($options->toArray() as $group => $groupSettings) {
            $this->settings[$group] = $groupSettings;
        }

        return $this;
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
        $profileObject = ConfigConverterTools::createObject(self::class);
        $expr = $profileObject;

        $this->addFormattersToExpr($expr);
        $this->addFiltersToExpr($expr);
        $this->addUnusedDefinitionsToExpr($expr);
        $this->addPathOptionsToExpr($expr);
        $this->addTesterOptionsToExpr($expr);
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
                    self::class,
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
                    JUnitFormatter::NAME => $formatterSettings === true ? new JUnitFormatter() : new JUnitFormatter(...$formatterSettings),
                    default => $formatterSettings === true ? new Formatter($name) : new Formatter($name, $formatterSettings),
                };
                $expr = ConfigConverterTools::addMethodCall(
                    self::class,
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
                    self::class,
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
            self::class,
            self::UNUSED_DEFINITIONS_FUNCTION,
            [$this->settings[self::DEFINITIONS_SETTING][self::PRINT_UNUSED_DEFINITIONS_SETTING]],
            $expr
        );
        unset($this->settings[self::DEFINITIONS_SETTING][self::PRINT_UNUSED_DEFINITIONS_SETTING]);
        if ($this->settings[self::DEFINITIONS_SETTING] === []) {
            unset($this->settings[self::DEFINITIONS_SETTING]);
        }
    }

    private function addPathOptionsToExpr(Expr &$expr): void
    {
        $args = [
            self::PRINT_ABSOLUTE_PATHS_PARAMETER => false,
            self::EDITOR_URL_PARAMETER => null,
            self::REMOVE_PREFIX_PARAMETER => [],
        ];
        $settingsPerParameter = [
            self::PRINT_ABSOLUTE_PATHS_PARAMETER => self::PRINT_ABSOLUTE_PATHS_SETTING,
            self::EDITOR_URL_PARAMETER => self::EDITOR_URL_SETTING,
            self::REMOVE_PREFIX_PARAMETER => self::REMOVE_PREFIX_SETTING,
        ];
        $settingFound = false;
        foreach ($settingsPerParameter as $parameter => $setting) {
            if (isset($this->settings[self::PATH_OPTIONS_SETTING][$setting])) {
                $args[$parameter] = $this->settings[self::PATH_OPTIONS_SETTING][$setting];
                unset($this->settings[self::PATH_OPTIONS_SETTING][$setting]);
                $settingFound = true;
            }
        }

        if ($settingFound) {
            $expr = ConfigConverterTools::addMethodCall(
                self::class,
                self::PATH_OPTIONS_FUNCTION,
                $args,
                $expr
            );

            if ($this->settings[self::PATH_OPTIONS_SETTING] === []) {
                unset($this->settings[self::PATH_OPTIONS_SETTING]);
            }
        }
    }

    private function addTesterOptionsToExpr(Expr &$expr): void
    {
        $optionsObject = TesterOptions::consumeSettingsFromProfile($this->settings);
        if ($optionsObject === null) {
            return;
        }

        $expr = ConfigConverterTools::addMethodCall(
            self::class,
            self::TESTER_OPTIONS_FUNCTION,
            [$optionsObject->toPhpExpr()],
            $expr,
        );
    }

    private function addExtensionsToExpr(Expr &$expr): void
    {
        if (!isset($this->settings[self::EXTENSIONS_SETTING])) {
            return;
        }
        foreach ($this->settings[self::EXTENSIONS_SETTING] as $name => $extensionSettings) {
            $extensionObject = new Extension($name, $extensionSettings ?? []);

            $expr = ConfigConverterTools::addMethodCall(
                self::class,
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
                self::class,
                self::SUITE_FUNCTION,
                [$suiteObject->toPhpExpr()],
                $expr
            );
        }
        unset($this->settings[self::SUITES_SETTING]);
    }
}
