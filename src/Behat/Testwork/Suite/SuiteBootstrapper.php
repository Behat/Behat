<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Suite;

use Behat\Testwork\Suite\Setup\SuiteSetup;

/**
 * Configures provided suites using registered suite setups.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class SuiteBootstrapper
{
    /**
     * @var SuiteSetup[]
     */
    private $setups = array();

    /**
     * Registers suite setup.
     *
     * @param SuiteSetup $setup
     */
    public function registerSuiteSetup(SuiteSetup $setup)
    {
        $this->setups[] = $setup;
    }

    /**
     * Bootstraps provided suites using registered setups.
     *
     * @param Suite[] $suites
     */
    public function bootstrapSuites(array $suites)
    {
        array_map(array($this, 'bootstrapSuite'), $suites);
    }

    /**
     * Bootstraps provided suite using registered setup.
     *
     * @param Suite $suite
     */
    public function bootstrapSuite(Suite $suite)
    {
        foreach ($this->setups as $setup) {
            if ($setup->supportsSuite($suite)) {
                $setup->setupSuite($suite);
            }
        }
    }
}
