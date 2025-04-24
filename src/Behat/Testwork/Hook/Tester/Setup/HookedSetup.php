<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Hook\Tester\Setup;

use Behat\Testwork\Call\CallResults;
use Behat\Testwork\Tester\Setup\Setup;

/**
 * Represents hooked test setup.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class HookedSetup implements Setup
{
    /**
     * @var Setup
     */
    private $setup;
    /**
     * @var CallResults
     */
    private $hookCallResults;

    /**
     * Initializes setup.
     */
    public function __construct(Setup $setup, CallResults $hookCallResults)
    {
        $this->setup = $setup;
        $this->hookCallResults = $hookCallResults;
    }

    public function isSuccessful()
    {
        if ($this->hookCallResults->hasExceptions()) {
            return false;
        }

        return $this->setup->isSuccessful();
    }

    public function hasOutput()
    {
        return $this->hookCallResults->hasStdOuts() || $this->hookCallResults->hasExceptions();
    }

    /**
     * Returns hook call results.
     *
     * @return CallResults
     */
    public function getHookCallResults()
    {
        return $this->hookCallResults;
    }
}
