<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Suite;

use Behat\Testwork\Suite\Exception\SuiteConfigurationException;
use Behat\Testwork\Suite\Exception\SuiteGenerationException;
use Behat\Testwork\Suite\Generator\SuiteGenerator;

/**
 * Acts like a suite repository by auto-generating suites for registered suite configurations using registered
 * generators.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class SuiteRegistry implements SuiteRepository
{
    /**
     * @var bool
     */
    private $suitesGenerated = false;
    /**
     * @var SuiteGenerator[]
     */
    private $generators = [];
    /**
     * @var array
     */
    private $suiteConfigurations = [];
    /**
     * @var Suite[]
     */
    private $suites = [];

    /**
     * Registers suite generator.
     */
    public function registerSuiteGenerator(SuiteGenerator $generator): void
    {
        $this->generators[] = $generator;
        $this->suitesGenerated = false;
    }

    /**
     * Registers suite using provided name, type & parameters.
     *
     * @param string $name
     * @param string $type
     *
     * @throws SuiteConfigurationException
     */
    public function registerSuiteConfiguration($name, $type, array $settings): void
    {
        if (isset($this->suiteConfigurations[$name])) {
            throw new SuiteConfigurationException(sprintf(
                'Suite configuration for a suite "%s" is already registered.',
                $name
            ), $name);
        }

        $this->suiteConfigurations[$name] = [$type, $settings];
        $this->suitesGenerated = false;
    }

    /**
     * Returns all available suites.
     *
     * @return Suite[]
     */
    public function getSuites()
    {
        if ($this->suitesGenerated) {
            return $this->suites;
        }

        $this->suites = [];
        foreach ($this->suiteConfigurations as $name => $configuration) {
            [$type, $settings] = $configuration;

            $this->suites[] = $this->generateSuite($name, $type, $settings);
        }

        $this->suitesGenerated = true;

        return $this->suites;
    }

    /**
     * Generates suite using registered generators.
     *
     * @throws SuiteGenerationException If no appropriate generator found
     */
    private function generateSuite(string $name, ?string $type, array $settings): Suite
    {
        foreach ($this->generators as $generator) {
            if (!$generator->supportsTypeAndSettings($type, $settings)) {
                continue;
            }

            return $generator->generateSuite($name, $settings);
        }

        throw new SuiteGenerationException(sprintf(
            'Can not find suite generator for a suite `%s` of type `%s`.',
            $name,
            $type
        ), $name);
    }
}
