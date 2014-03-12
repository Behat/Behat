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
     * @var Environment
     */
    private $environment;
    /**
     * @var StepNode
     */
    private $step;

    /**
     * Initializes undefined step.
     *
     * @param Environment $environment
     * @param StepNode    $step
     */
    public function __construct(Environment $environment, StepNode $step)
    {
        $this->environment = $environment;
        $this->step = $step;
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
