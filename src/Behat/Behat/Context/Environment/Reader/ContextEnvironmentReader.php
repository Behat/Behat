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
    private $contextReaders = [];

    /**
     * Registers context loader.
     */
    public function registerContextReader(ContextReader $contextReader): void
    {
        $this->contextReaders[] = $contextReader;
    }

    public function supportsEnvironment(Environment $environment): bool
    {
        return $environment instanceof ContextEnvironment;
    }

    /**
     * @return list<Callee>
     */
    public function readEnvironmentCallees(Environment $environment): array
    {
        if (!$environment instanceof ContextEnvironment) {
            throw new EnvironmentReadException(sprintf(
                'ContextEnvironmentReader does not support `%s` environment.',
                $environment::class
            ), $environment);
        }

        $callees = [];
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
     * @param string             $contextClass
     *
     * @return list<Callee>
     */
    private function readContextCallees(ContextEnvironment $environment, $contextClass): array
    {
        $callees = [];
        foreach ($this->contextReaders as $loader) {
            $callees = array_merge(
                $callees,
                $loader->readContextCallees($environment, $contextClass)
            );
        }

        return $callees;
    }
}
