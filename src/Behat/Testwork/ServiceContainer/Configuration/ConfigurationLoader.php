<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\ServiceContainer\Configuration;

use Behat\Config\ConfigInterface;
use Behat\Testwork\ServiceContainer\Exception\ConfigurationLoadingException;
use Closure;

use function str_ends_with;

/**
 * Loads configuration from different sources.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ConfigurationLoader
{
    private ?bool $profileFound = null;
    private array $debugInformation = [
        'environment_variable_name' => 'none',
        'environment_variable_content' => 'none',
        'configuration_file_path' => 'none',
    ];

    /**
     * Constructs reader.
     *
     * @param string|null $environmentVariable Environment variable name
     * @param string|null $configurationPath       Configuration file path
     */
    public function __construct(
        private ?string $environmentVariable = null,
        private ?string $configurationPath = null,
    ) {
    }

    /**
     * Sets environment variable name.
     */
    public function setEnvironmentVariableName(?string $variable): void
    {
        $this->environmentVariable = $variable;
    }

    /**
     * Returns environment variable name.
     */
    public function getEnvironmentVariableName(): ?string
    {
        return $this->environmentVariable;
    }

    /**
     * Sets configuration file path.
     */
    public function setConfigurationFilePath(?string $path): void
    {
        $this->configurationPath = $path;
    }

    /**
     * Returns configuration file path.
     */
    public function getConfigurationFilePath(): ?string
    {
        return $this->configurationPath;
    }

    /**
     * Reads configuration sequence for specific profile.
     *
     * @param string $profile Profile name
     *
     * @return list<mixed>
     *
     * @throws ConfigurationLoadingException
     */
    public function loadConfiguration(string $profile = 'default'): array
    {
        $this->profileFound = false;

        $configs = $this->loadEnvironmentConfiguration();

        // second is file configuration (if there is some)
        if ($this->configurationPath) {
            $this->debugInformation['configuration_file_path'] = $this->configurationPath;

            foreach ($this->loadFileConfiguration($this->configurationPath, $profile) as $config) {
                $configs[] = $config;
            }
        }

        // if specific profile has not been found
        if ('default' !== $profile && !$this->profileFound) {
            throw new ConfigurationLoadingException(sprintf(
                'Can not find configuration for `%s` profile.',
                $profile
            ));
        }

        return $configs;
    }

    /**
     * Returns array with configuration debug information.
     */
    public function debugInformation(): array
    {
        return $this->debugInformation;
    }

    /**
     * Loads information from environment variable.
     *
     * @return list<mixed>
     *
     * @throws ConfigurationLoadingException If environment variable environment var is set to invalid JSON
     */
    private function loadEnvironmentConfiguration(): array
    {
        $configs = [];

        if (!$this->environmentVariable) {
            return $configs;
        }

        $this->debugInformation['environment_variable_name'] = $this->environmentVariable;

        if ($envConfig = getenv($this->environmentVariable)) {
            $config = @json_decode($envConfig, true);

            $this->debugInformation['environment_variable_content'] = $envConfig;

            if (!$config) {
                throw new ConfigurationLoadingException(sprintf(
                    'Environment variable `%s` should contain a valid JSON, but it is set to `%s`.',
                    $this->environmentVariable,
                    $envConfig
                ));
            }

            $configs[] = $config;
        }

        return $configs;
    }

    /**
     * Loads information from PHP configuration file.
     *
     * @param string $configPath Config file path
     * @param string $profile    Profile name
     *
     * @throws ConfigurationLoadingException If config file is not found or is not a PHP file
     *
     * @phpstan-impure
     */
    private function loadFileConfiguration(string $configPath, string $profile): array
    {
        if (!is_file($configPath) || !is_readable($configPath)) {
            throw new ConfigurationLoadingException(sprintf('Configuration file `%s` not found.', $configPath));
        }

        if (!str_ends_with($configPath, '.php')) {
            throw new ConfigurationLoadingException(sprintf('Configuration file `%s` must be a PHP file.', $configPath));
        }

        $basePath = rtrim(dirname($configPath), DIRECTORY_SEPARATOR);

        $phpConfig = $this->getPHPConfigObjectClosure($configPath)();

        if (!$phpConfig instanceof ConfigInterface) {
            throw new ConfigurationLoadingException(sprintf('Configuration file `%s` must return an instance of `%s`.', $configPath, ConfigInterface::class));
        }

        $config = $phpConfig->toArray();

        return $this->loadConfigs($basePath, $config, $profile);
    }

    /**
     * Scope isolated include.
     *
     * Prevents access to $this/self from included files.
     */
    private function getPHPConfigObjectClosure(string $configPath): Closure
    {
        return Closure::bind(function () use ($configPath): mixed {
            $config = require $configPath;

            return $config;
        }, null, null);
    }

    /**
     * Loads configs for provided config and profile.
     */
    private function loadConfigs(string $basePath, array $config, string $profile): array
    {
        $configs = [];

        $profile = $this->getProfileName($config, $profile);

        // first load default profile from current config, but only if custom profile requested
        if ('default' !== $profile && isset($config['default'])) {
            $configs[] = $config['default'];
        }

        // then recursively load profiles from imports
        if (isset($config['imports']) && is_array($config['imports'])) {
            $configs = array_merge($configs, $this->loadImports($basePath, $config['imports'], $profile));
        }

        // then load specific profile from current config
        if (isset($config[$profile])) {
            $configs[] = $config[$profile];
            $this->profileFound = true;
        }

        if (!$this->profileFound && isset($config['preferredProfileName'])) {
            throw new ConfigurationLoadingException(sprintf(
                'Can not find configuration for `%s` profile.',
                $config['preferredProfileName']
            ));
        }

        return $configs;
    }

    /**
     * Get the name of the requested profile, after considering any preferred profile name.
     */
    private function getProfileName(array $config, string $profile): string
    {
        if (isset($config['preferredProfileName']) && 'default' === $profile) {
            return $config['preferredProfileName'];
        }

        return $profile;
    }

    /**
     * Loads all provided imports.
     */
    private function loadImports(string $basePath, array $paths, string $profile): array
    {
        $configs = [];
        foreach ($paths as $path) {
            foreach ($this->parseImport($basePath, $path, $profile) as $importConfig) {
                $configs[] = $importConfig;
            }
        }

        return $configs;
    }

    /**
     * Parses import.
     *
     * @throws ConfigurationLoadingException If import file not found
     */
    private function parseImport(string $basePath, string $path, string $profile): array
    {
        if (!file_exists($path) && file_exists($basePath . DIRECTORY_SEPARATOR . $path)) {
            $path = $basePath . DIRECTORY_SEPARATOR . $path;
        }

        if (!file_exists($path)) {
            throw new ConfigurationLoadingException(sprintf(
                'Can not import `%s` configuration file. File not found.',
                $path
            ));
        }

        return $this->loadFileConfiguration($path, $profile);
    }
}
