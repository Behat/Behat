<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Environment;

use Behat\Testwork\Call\Callee;
use Behat\Testwork\Environment\Exception\EnvironmentBuildException;
use Behat\Testwork\Environment\Exception\EnvironmentIsolationException;
use Behat\Testwork\Environment\Handler\EnvironmentHandler;
use Behat\Testwork\Environment\Reader\EnvironmentReader;
use Behat\Testwork\Suite\Suite;

/**
 * Builds, isolates and reads environments using registered handlers and readers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class EnvironmentManager
{
    /**
     * @var EnvironmentHandler[]
     */
    private $handlers = [];
    /**
     * @var EnvironmentReader[]
     */
    private $readers = [];

    /**
     * Registers environment handler.
     */
    public function registerEnvironmentHandler(EnvironmentHandler $handler): void
    {
        $this->handlers[] = $handler;
    }

    /**
     * Registers environment reader.
     */
    public function registerEnvironmentReader(EnvironmentReader $reader): void
    {
        $this->readers[] = $reader;
    }

    /**
     * Builds new environment for provided test suite.
     *
     * @return Environment
     *
     * @throws EnvironmentBuildException
     */
    public function buildEnvironment(Suite $suite)
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supportsSuite($suite)) {
                return $handler->buildEnvironment($suite);
            }
        }

        throw new EnvironmentBuildException(sprintf(
            'None of the registered environment handlers seem to support `%s` suite.',
            $suite->getName()
        ), $suite);
    }

    /**
     * Creates new isolated test environment using built one.
     *
     * @return Environment
     *
     * @throws EnvironmentIsolationException If appropriate environment handler is not found
     */
    public function isolateEnvironment(Environment $environment, $testSubject = null)
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supportsEnvironmentAndSubject($environment, $testSubject)) {
                return $handler->isolateEnvironment($environment, $testSubject);
            }
        }

        throw new EnvironmentIsolationException(sprintf(
            'None of the registered environment handlers seem to support `%s` environment.',
            $environment::class
        ), $environment, $testSubject);
    }

    /**
     * Reads all callees from environment using registered environment readers.
     *
     * @return Callee[]
     */
    public function readEnvironmentCallees(Environment $environment): array
    {
        $callees = [];
        foreach ($this->readers as $reader) {
            if ($reader->supportsEnvironment($environment)) {
                $callees = array_merge($callees, $reader->readEnvironmentCallees($environment));
            }
        }

        return $callees;
    }
}
