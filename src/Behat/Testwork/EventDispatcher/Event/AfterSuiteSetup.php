<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\EventDispatcher\Event;

use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Specification\SpecificationIterator;
use Behat\Testwork\Tester\Setup\Setup;

/**
 * Represents an event right after a suite setup.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class AfterSuiteSetup extends SuiteTested implements AfterSetup
{
    /**
     * Initializes event.
     *
     * @param SpecificationIterator<mixed> $iterator
     */
    public function __construct(
        Environment $env,
        private readonly SpecificationIterator $iterator,
        private readonly Setup $setup,
    ) {
        parent::__construct($env);
    }

    public function getSpecificationIterator(): SpecificationIterator
    {
        return $this->iterator;
    }

    /**
     * Returns current test setup.
     */
    public function getSetup(): Setup
    {
        return $this->setup;
    }
}
