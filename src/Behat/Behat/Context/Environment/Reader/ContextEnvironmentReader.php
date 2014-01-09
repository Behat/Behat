<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Environment\Reader;

use Behat\Behat\Context\Environment\ContextEnvironment;
use Behat\Behat\Context\Reader\ContextReader;
use Behat\Testwork\Call\Callee;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Environment\Reader\EnvironmentReader;

/**
 * Context-based environment reader.
 *
 * Reads context environment callees using registered context loaders.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ContextEnvironmentReader implements EnvironmentReader
{
    /**
     * @var ContextReader[]
     */
    private $contextReaders = array();
    /**
     * @var Callee[][]
     */
    private $callees = array();

    /**
     * Registers context loader.
     *
     * @param ContextReader $contextReader
     */
    public function registerContextReader(ContextReader $contextReader)
    {
        $this->contextReaders[] = $contextReader;
        $this->callees = array();
    }

    /**
     * Checks if reader supports an environment.
     *
     * @param Environment $environment
     *
     * @return Boolean
     */
    public function supportsEnvironment(Environment $environment)
    {
        return $environment instanceof ContextEnvironment;
    }

    /**
     * Reads callees from an environment.
     *
     * @param ContextEnvironment $environment
     *
     * @return Callee[]
     */
    public function readEnvironmentCallees(Environment $environment)
    {
        $callees = array();
        foreach ($environment->getContextClasses() as $contextClass) {
            $callees = array_merge(
                $callees,
                $this->readContextCallees($environment, $contextClass)
            );
        }

        return $callees;
    }

    /**
     * Reads callees from a specific suite's context.
     *
     * @param Environment $environment
     * @param string      $contextClass
     *
     * @return Callee[]
     */
    protected function readContextCallees(Environment $environment, $contextClass)
    {
        if (isset($this->callees[$contextClass])) {
            return $this->callees[$contextClass];
        }

        $callees = array();
        foreach ($this->contextReaders as $loader) {
            $callees = array_merge(
                $callees,
                $loader->readContextCallees($environment, $contextClass)
            );
        }

        return $this->callees[$contextClass] = $callees;
    }
}
