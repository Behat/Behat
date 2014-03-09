<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Hook\Scope;

use Behat\Testwork\Specification\SpecificationIterator;
use Behat\Testwork\Suite\Suite;
use Behat\Testwork\Environment\Environment;

/**
 * Testwork before suite hook scope.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class BeforeSuiteScope implements SuiteScope
{
    /**
     * @var Environment
     */
    private $environment;
    /**
     * @var SpecificationIterator
     */
    private $iterator;

    /**
     * Initializes scope.
     *
     * @param Environment           $env
     * @param SpecificationIterator $iterator
     */
    public function __construct(Environment $env, SpecificationIterator $iterator)
    {
        $this->environment = $env;
        $this->iterator = $iterator;
    }

    /**
     * Returns hook scope name.
     *
     * @return string
     */
    public function getName()
    {
        return self::BEFORE;
    }

    /**
     * Returns hook suite.
     *
     * @return Suite
     */
    public function getSuite()
    {
        return $this->environment->getSuite();
    }

    /**
     * Returns hook environment.
     *
     * @return Environment
     */
    public function getEnvironment()
    {
        return $this->environment;
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
}
