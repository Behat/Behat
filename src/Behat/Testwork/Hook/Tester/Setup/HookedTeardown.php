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
use Behat\Testwork\Tester\Setup\Teardown;

/**
 * Represents hooked test teardown.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class HookedTeardown implements Teardown
{
    /**
     * Initializes setup.
     */
    public function __construct(
        private readonly Teardown $teardown,
        private readonly CallResults $hookCallResults,
    ) {
    }

    public function isSuccessful()
    {
        if ($this->hookCallResults->hasExceptions()) {
            return false;
        }

        return $this->teardown->isSuccessful();
    }

    public function hasOutput(): bool
    {
        return $this->hookCallResults->hasStdOuts() || $this->hookCallResults->hasExceptions();
    }

    /**
     * Returns hook call results.
     */
    public function getHookCallResults(): CallResults
    {
        return $this->hookCallResults;
    }
}
