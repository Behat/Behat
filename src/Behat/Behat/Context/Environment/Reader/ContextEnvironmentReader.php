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
use Behat\Testwork\Environment\Exception\EnvironmentReadException;
use Behat\Testwork\Environment\Reader\EnvironmentReader;

/**
 * Reads context-based environment callees using registered context loaders.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ContextEnvironmentReader implements EnvironmentReader
{
    /**
     * @var ContextReader[]
     */
    private $contextReaders = array();

    /**
     * Registers context loader.
     *
     * @param ContextReader $contextReader
     */
    public function registerContextReader(ContextReader $contextReader)
    {
        $this->contextReaders[] = $contextReader;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsEnvironment(Environment $environment)
    {
        return $environment instanceof ContextEnvironment;
    }

    /**
     * {@inheritdoc}
     */
    public function readEnvironmentCallees(Environment $environment)
    {
        if (!$environment instanceof ContextEnvironment) {
            throw new EnvironmentReadException(sprintf(
                'ContextEnvironmentReader does not support `%s` environment.',
                get_class($environment)
            ), $environment);
        }

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
     * @param ContextEnvironment $environment
     * @param string             $contextClass
     *
     * @return Callee[]
     */
    private function readContextCallees(ContextEnvironment $environment, $contextClass)
    {
        $callees = array();
        foreach ($this->contextReaders as $loader) {
            $callees = array_merge(
                $callees,
                $loader->readContextCallees($environment, $contextClass)
            );
        }

        return $callees;
    }
}
