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
     * Initializes call.
     *
     * @param int|null $errorReportingLevel
     */
    public function __construct(
        private readonly Environment $environment,
        private readonly Callee $callee,
        private readonly array $arguments,
        private $errorReportingLevel = null,
    ) {
    }

    /**
     * Returns environment this call is executed from.
     */
    final public function getEnvironment(): Environment
    {
        return $this->environment;
    }

    final public function getCallee(): Callee
    {
        return $this->callee;
    }

    final public function getBoundCallable()
    {
        return $this->environment->bindCallee($this->callee);
    }

    final public function getArguments(): array
    {
        return $this->arguments;
    }

    final public function getErrorReportingLevel()
    {
        return $this->errorReportingLevel;
    }
}
