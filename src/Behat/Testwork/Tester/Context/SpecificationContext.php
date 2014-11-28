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

/**
 * Encapsulates a specification context.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class SpecificationContext implements Context
{
    /**
     * @var mixed
     */
    private $specification;
    /**
     * @var Environment
     */
    private $environment;

    /**
     * Initializes context.
     *
     * @param mixed       $specification
     * @param Environment $environment
     */
    public function __construct($specification, Environment $environment)
    {
        $this->specification = $specification;
        $this->environment = $environment;
    }

    /**
     * Returns specification.
     *
     * @return mixed
     */
    public function getSpecification()
    {
        return $this->specification;
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
