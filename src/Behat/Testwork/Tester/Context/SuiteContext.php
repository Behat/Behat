<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester\Context;

use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Specification\SpecificationIterator;

/**
 * Represents a context for specification suite tests.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class SuiteContext implements Context
{
    /**
     * @var SpecificationIterator
     */
    private $iterator;
    /**
     * @var Environment
     */
    private $environment;

    /**
     * Initializes context.
     *
     * @param SpecificationIterator $iterator
     * @param Environment           $environment
     */
    public function __construct(SpecificationIterator $iterator, Environment $environment)
    {
        $this->iterator = $iterator;
        $this->environment = $environment;
    }

    /**
     * Returns specification iterator.
     *
     * @return SpecificationIterator
     */
    public function getSpecificationIterator()
    {
        return $this->iterator;
    }

    /**
     * Returns environment.
     *
     * @return Environment
     */
    public function getEnvironment()
    {
        return $this->environment;
    }
}
