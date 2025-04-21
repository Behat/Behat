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
 * Represents environment-based call.
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
     * @var int|null
     */
    private $errorReportingLevel;

    /**
     * Initializes call.
     *
     * @param int|null $errorReportingLevel
     */
    public function __construct(
        Environment $environment,
        Callee $callee,
        array $arguments,
        $errorReportingLevel = null,
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
    final public function getEnvironment()
    {
        return $this->environment;
    }

    final public function getCallee()
    {
        return $this->callee;
    }

    final public function getBoundCallable()
    {
        return $this->environment->bindCallee($this->callee);
    }

    final public function getArguments()
    {
        return $this->arguments;
    }

    final public function getErrorReportingLevel()
    {
        return $this->errorReportingLevel;
    }
}
