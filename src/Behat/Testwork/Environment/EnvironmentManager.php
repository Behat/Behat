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
 * Testwork test environment manager.
 *
 * Builds, isolates and reads environments using registered handlers and readers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class EnvironmentManager
{
    /**
     * @var EnvironmentHandler[]
     */
    private $handlers = array();
    /**
     * @var EnvironmentReader[]
     */
    private $readers = array();

    /**
     * Registers environment handler.
     *
     * @param EnvironmentHandler $handler
     */
    public function registerEnvironmentHandler(EnvironmentHandler $handler)
    {
        $this->handlers[] = $handler;
    }

    /**
     * Registers environment reader.
     *
     * @param EnvironmentReader $reader
     */
    public function registerEnvironmentReader(EnvironmentReader $reader)
    {
        $this->readers[] = $reader;
    }

    /**
     * Builds new environment for provided test suite.
     *
     * @param Suite      $suite
     * @param null|mixed $testSubject
     *
     * @return Environment
     *
     * @throws EnvironmentBuildException
     */
    public function buildEnvironment(Suite $suite, $testSubject = null)
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supportsSuiteAndSubject($suite, $testSubject)) {
                return $handler->buildEnvironment($suite, $testSubject);
            }
        }

        throw new EnvironmentBuildException(sprintf(
            'None of the registered environment handlers seem to support `%s` suite.',
            $suite->getName()
        ), $suite, $testSubject);
    }

    /**
     * Creates new isolated test environment using built one.
     *
     * @param Environment $environment
     *
     * @return Environment
     *
     * @throws EnvironmentIsolationException If appropriate environment handler is not found
     */
    public function isolateEnvironment(Environment $environment)
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supportsEnvironment($environment)) {
                return $handler->isolateEnvironment($environment);
            }
        }

        throw new EnvironmentIsolationException(sprintf(
            'None of the registered environment handlers seem to support `%s` environment.',
            get_class($environment)
        ), $environment);
    }

    /**
     * Reads all callees from environment using registered environment readers.
     *
     * @param Environment $environment
     *
     * @return Callee[]
     */
    public function readEnvironmentCallees(Environment $environment)
    {
        $callees = array();
        foreach ($this->readers as $reader) {
            if ($reader->supportsEnvironment($environment)) {
                $callees = array_merge($callees, $reader->readEnvironmentCallees($environment));
            }
        }

        return $callees;
    }
}
