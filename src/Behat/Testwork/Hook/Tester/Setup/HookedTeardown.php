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
     * @var Teardown
     */
    private $teardown;
    /**
     * @var CallResults
     */
    private $hookCallResults;

    /**
     * Initializes setup.
     *
     * @param Teardown    $teardown
     * @param CallResults $hookCallResults
     */
    public function __construct(Teardown $teardown, CallResults $hookCallResults)
    {
        $this->teardown = $teardown;
        $this->hookCallResults = $hookCallResults;
    }

    /**
     * {@inheritdoc}
     */
    public function isSuccessful()
    {
        if ($this->hookCallResults->hasExceptions()) {
            return false;
        }

        return $this->teardown->isSuccessful();
    }

    /**
     * {@inheritdoc}
     */
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
