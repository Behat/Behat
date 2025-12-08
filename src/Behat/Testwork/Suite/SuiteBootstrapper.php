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
    private $setups = [];

    /**
     * Registers suite setup.
     */
    public function registerSuiteSetup(SuiteSetup $setup): void
    {
        $this->setups[] = $setup;
    }

    /**
     * Bootstraps provided suites using registered setups.
     *
     * @param Suite[] $suites
     */
    public function bootstrapSuites(array $suites): void
    {
        array_map($this->bootstrapSuite(...), $suites);
    }

    /**
     * Bootstraps provided suite using registered setup.
     */
    public function bootstrapSuite(Suite $suite): void
    {
        foreach ($this->setups as $setup) {
            if ($setup->supportsSuite($suite)) {
                $setup->setupSuite($suite);
            }
        }
    }
}
