<?php

declare(strict_types=1);

namespace Behat\Config;

use Behat\Config\Filter\FilterInterface;
use Behat\Config\Formatter\FormatterConfigInterface;
use Behat\Testwork\ServiceContainer\Exception\ConfigurationLoadingException;

final class Profile
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

    public function __construct(
        private readonly string $name,
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

        if ($removePrefix !== []) {
            $this->settings[self::PATH_OPTIONS_SETTING][self::REMOVE_PREFIX_SETTING] = $removePrefix;
        }

        return $this;
    }

    public function withTesterOptions(TesterOptions $testerOptions): self
    {
        $this->settings = array_merge($this->settings, $testerOptions->toArray());

        return $this;
    }

    public function toArray(): array
    {
        return $this->settings;
    }
}
