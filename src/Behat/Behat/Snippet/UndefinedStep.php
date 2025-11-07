<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Snippet;

use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Environment\Environment;

/**
 * Represents an undefined step in a specific environment.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class UndefinedStep
{
    /**
     * Initializes undefined step.
     */
    public function __construct(
        private readonly Environment $environment,
        private readonly StepNode $step,
    ) {
    }

    /**
     * Returns environment that needs this step.
     *
     * @return Environment
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Returns undefined step node.
     *
     * @return StepNode
     */
    public function getStep()
    {
        return $this->step;
    }
}
