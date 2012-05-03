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
    private $configFile;
    private $profile;

    /**
     * Constructs reader.
     *
     * @param string $configFile Configuration file path
     */
    public function __construct($configFile = null)
    {
        $this->configFile = $configFile;
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

        // first is ENV config
        foreach ($this->loadEnvironmentConfiguration($profile) as $config) {
            $configs[] = $config;
        }

        // second is file configuration (if there is some)
        if (null !== $this->configFile && file_exists($this->configFile)) {
            foreach ($this->loadFileConfiguration($this->configFile, $profile) as $config) {
                $configs[] = $config;
            }
        }

        return $configs;
    }

    /**
     * Loads information from ENV variable.
     *
     * @param string $profile Profile name
     *
     * @return array
     */
    protected function loadEnvironmentConfiguration($profile)
    {
        $configs = array();
        if ($envConfig = getenv('BEHAT_PARAMS')) {
            parse_str($envConfig, $config);
            $configs[] = $this->normalizeRawConfiguration($config);
        }

        return $configs;
    }

    /**
     * Loads information from YAML configuration file.
     *
     * @param string $configFile Config file path
     * @param string $profile    Profile name
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    protected function loadFileConfiguration($configFile, $profile)
    {
        if (!is_file($configFile) || !is_readable($configFile)) {
            throw new \InvalidArgumentException("Config file \"$configFile\" not found");
        }

        $config  = Yaml::parse($configFile);
        $configs = array();

        if (isset($config['default'])) {
            $configs[] = $config['default'];
        }
        if ('default' !== $profile && isset($config[$profile])) {
            $configs[] = $config[$profile];
        }

        if (isset($config['imports']) && is_array($config['imports'])) {
            foreach ($config['imports'] as $path) {
                foreach ($this->parseImport($path, $profile) as $importConfig) {
                    $configs[] = $importConfig;
                }
            }
        }

        return $configs;
    }

    private function parseImport($path, $profile)
    {
        if (!file_exists($path) && file_exists(getcwd().DIRECTORY_SEPARATOR.$path)) {
            $path = getcwd().DIRECTORY_SEPARATOR.$path;
        }

        if (!file_exists($path)) {
            foreach (explode(PATH_SEPARATOR, get_include_path()) as $libPath) {
                if (file_exists($libPath.DIRECTORY_SEPARATOR.$path)) {
                    $path = $libPath.DIRECTORY_SEPARATOR.$path;
                    break;
                }
            }
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
