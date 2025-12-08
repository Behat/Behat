<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\EventDispatcher\Event;

use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\EventDispatcher\Event\AfterSetup;
use Behat\Testwork\Tester\Setup\Setup;

/**
 * Represents an event after step setup.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class AfterStepSetup extends StepTested implements AfterSetup
{
    /**
     * Initializes event.
     */
    public function __construct(
        Environment $env,
        private readonly FeatureNode $feature,
        private readonly StepNode $step,
        private readonly Setup $setup,
    ) {
        parent::__construct($env);
    }

    /**
     * Returns feature.
     */
    public function getFeature(): FeatureNode
    {
        return $this->feature;
    }

    /**
     * Returns step node.
     */
    public function getStep(): StepNode
    {
        return $this->step;
    }

    /**
     * Returns current test setup.
     */
    public function getSetup(): Setup
    {
        return $this->setup;
    }

    /**
     * Checks if step call, setup or teardown produced any output (stdOut or exception).
     *
     * @return bool
     */
    public function hasOutput()
    {
        return $this->setup->hasOutput();
    }
}
