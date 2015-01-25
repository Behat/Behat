<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\ServiceContainer\Configuration;

use Behat\Testwork\ServiceContainer\Exception\ConfigurationLoadingException;
use Symfony\Component\Yaml\Yaml;

/**
 * Loads configuration from different sources.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ConfigurationLoader
{
    /**
     * @var null|string
     */
    private $configurationPath;
    /**
     * @var null|string
     */
    private $environmentVariable;
    /**
     * @var Boolean
     */
    private $profileFound;

    /**
     * Constructs reader.
     *
     * @param string $environmentVariableName Environment variable name
     * @param string $configurationPath       Configuration file path
     */
    public function __construct($environmentVariableName = null, $configurationPath = null)
    {
        $this->environmentVariable = $environmentVariableName;
        $this->configurationPath = $configurationPath;
    }

    /**
     * Sets environment variable name.
     *
     * @param null|string $variable
     */
    public function setEnvironmentVariableName($variable)
    {
        $this->environmentVariable = $variable;
    }

    /**
     * Returns environment variable name.
     *
     * @return null|string
     */
    public function getEnvironmentVariableName()
    {
        return $this->environmentVariable;
    }

    /**
     * Sets configuration file path.
     *
     * @param null|string $path
     */
    public function setConfigurationFilePath($path)
    {
        $this->configurationPath = $path;
    }

    /**
     * Returns configuration file path.
     *
     * @return null|string
     */
    public function getConfigurationFilePath()
    {
        return $this->configurationPath;
    }

    /**
     * Reads configuration sequence for specific profile.
     *
     * @param string $profile Profile name
     *
     * @return array
     *
     * @throws ConfigurationLoadingException
     */
    public function loadConfiguration($profile = 'default')
    {
        $configs = array();
        $this->profileFound = false;

        // first is ENV config
        foreach ($this->loadEnvironmentConfiguration() as $config) {
            $configs[] = $config;
        }

        // second is file configuration (if there is some)
        if ($this->configurationPath) {
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
     * Loads information from environment variable.
     *
     * @return array
     *
     * @throws ConfigurationLoadingException If environment variable environment var is set to invalid JSON
     */
    protected function loadEnvironmentConfiguration()
    {
        $configs = array();

        if (!$this->environmentVariable) {
            return $configs;
        }

        if ($envConfig = getenv($this->environmentVariable)) {
            $config = @json_decode($envConfig, true);

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
     * Loads information from YAML configuration file.
     *
     * @param string $configPath Config file path
     * @param string $profile    Profile name
     *
     * @return array
     *
     * @throws ConfigurationLoadingException If config file is not found
     */
    protected function loadFileConfiguration($configPath, $profile)
    {
        if (!is_file($configPath) || !is_readable($configPath)) {
            throw new ConfigurationLoadingException(sprintf('Configuration file `%s` not found.', $configPath));
        }

        $basePath = rtrim(dirname($configPath), DIRECTORY_SEPARATOR);
        $config = (array) Yaml::parse(file_get_contents($configPath));

        return $this->loadConfigs($basePath, $config, $profile);
    }

    /**
     * Loads configs for provided config and profile.
     *
     * @param string $basePath
     * @param array  $config
     * @param string $profile
     *
     * @return array
     */
    private function loadConfigs($basePath, array $config, $profile)
    {
        $configs = array();

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

        return $configs;
    }

    /**
     * Loads all provided imports.
     *
     * @param string $basePath
     * @param array  $paths
     * @param string $profile
     *
     * @return array
     */
    private function loadImports($basePath, array $paths, $profile)
    {
        $configs = array();
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
     * @param string $basePath
     * @param string $path
     * @param string $profile
     *
     * @return array
     *
     * @throws ConfigurationLoadingException If import file not found
     */
    private function parseImport($basePath, $path, $profile)
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
