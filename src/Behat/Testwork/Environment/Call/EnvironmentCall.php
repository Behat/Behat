<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Environment\Call;

use Behat\Testwork\Call\Call;
use Behat\Testwork\Call\Callee;
use Behat\Testwork\Environment\Environment;

/**
 * Testwork environment call.
 *
 * Implements environment-based call.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class EnvironmentCall implements Call
{
    /**
     * @var Environment
     */
    private $environment;
    /**
     * @var Callee
     */
    private $callee;
    /**
     * @var array
     */
    private $arguments;
    /**
     * @var null|integer
     */
    private $errorReportingLevel;

    /**
     * Initializes call.
     *
     * @param Environment  $environment
     * @param Callee       $callee
     * @param array        $arguments
     * @param null|integer $errorReportingLevel
     */
    public function __construct(
        Environment $environment,
        Callee $callee,
        array $arguments,
        $errorReportingLevel = null
    ) {
        $this->environment = $environment;
        $this->callee = $callee;
        $this->arguments = $arguments;
        $this->errorReportingLevel = $errorReportingLevel;
    }

    /**
     * Returns environment this call is executed from.
     *
     * @return Environment
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Returns callee.
     *
     * @return Callee
     */
    public function getCallee()
    {
        return $this->callee;
    }

    /**
     * Returns callable bound to environment.
     *
     * @return callable
     */
    public function getBoundCallable()
    {
        return $this->environment->bindCallee($this->callee);
    }

    /**
     * Returns call arguments.
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Returns call error reporting level.
     *
     * @return null|integer
     */
    public function getErrorReportingLevel()
    {
        return $this->errorReportingLevel;
    }
}
