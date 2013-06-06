<?php

namespace Behat\Behat\DependencyInjection\Configuration;

use Symfony\Component\Yaml\Yaml;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Behat configuration reader.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Loader
{
    private $configPath;
    private $profileFound;

    /**
     * Constructs reader.
     *
     * @param string $configPath Configuration file path
     */
    public function __construct($configPath = null)
    {
        $this->configPath = $configPath;
    }

    /**
     * Reads configuration sequense for specific profile.
     *
     * @param string $profile Profile name
     *
     * @return array
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
        if ($this->configPath) {
            foreach ($this->loadFileConfiguration($this->configPath, $profile) as $config) {
                $configs[] = $config;
            }
        }

        // if specific profile has not been found
        if ('default' !== $profile && !$this->profileFound) {
            throw new \RuntimeException(sprintf(
                'Configuration for profile "%s" can not be found.', $profile
            ));
        }

        return $configs;
    }

    /**
     * Loads information from ENV variable.
     *
     * @return array
     */
    protected function loadEnvironmentConfiguration()
    {
        $configs = array();
        if ($envConfig = getenv('BEHAT_PARAMS')) {
            if (null === $config = @json_decode($envConfig, true)) {
                @parse_str($envConfig, $config);
                $config = $this->normalizeRawConfiguration($config);
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
     * @throws \RuntimeException
     */
    protected function loadFileConfiguration($configPath, $profile)
    {
        if (!is_file($configPath) || !is_readable($configPath)) {
            throw new \RuntimeException("Config file \"$configPath\" not found");
        }

        $basePath = rtrim(dirname($configPath), DIRECTORY_SEPARATOR);
        $config   = Yaml::parse($configPath);
        $configs  = array();

        // first load default profile from current config, but only if custom profile requested
        if ('default' !== $profile && isset($config['default'])) {
            $configs[] = $config['default'];
        }

        // then recursively load profiles from imports
        if (isset($config['imports']) && is_array($config['imports'])) {
            foreach ($config['imports'] as $path) {
                foreach ($this->parseImport($basePath, $path, $profile) as $importConfig) {
                    $configs[] = $importConfig;
                }
            }
        }

        // then load specific profile from current config
        if (isset($config[$profile])) {
            $configs[] = $config[$profile];
            $this->profileFound = true;
        }

        return $configs;
    }

    private function parseImport($basePath, $path, $profile)
    {
        if (!file_exists($path) && file_exists($basePath.DIRECTORY_SEPARATOR.$path)) {
            $path = $basePath.DIRECTORY_SEPARATOR.$path;
        }

        if (!file_exists($path)) {
            throw new \RuntimeException(sprintf(
                'Can not import config "%s": file not found.', $path
            ));
        }

        return $this->loadFileConfiguration($path, $profile);
    }

    private function normalizeRawConfiguration(array $config)
    {
        $normalize = function($value) {
            if ('true' === $value || 'false' === $value) {
                return 'true' === $value;
            }

            if (is_numeric($value)) {
                return ctype_digit($value) ? intval($value) : floatval($value);
            }

            return $value;
        };

        if (isset($config['formatter']['parameters'])) {
            $config['formatter']['parameters'] = array_map(
                $normalize, $config['formatter']['parameters']
            );
        }

        if (isset($config['context']['parameters'])) {
            $config['context']['parameters'] = array_map(
                $normalize, $config['context']['parameters']
            );
        }

        return $config;
    }
}
