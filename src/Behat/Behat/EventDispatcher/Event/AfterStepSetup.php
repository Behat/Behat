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
     * @var FeatureNode
     */
    private $feature;
    /**
     * @var StepNode
     */
    private $step;
    /**
     * @var Setup
     */
    private $setup;

    /**
     * Initializes event.
     *
     * @param Environment $env
     * @param FeatureNode $feature
     * @param StepNode    $step
     * @param Setup       $setup
     */
    public function __construct(Environment $env, FeatureNode $feature, StepNode $step, Setup $setup)
    {
        parent::__construct($env);

        $this->feature = $feature;
        $this->step = $step;
        $this->setup = $setup;
    }

    /**
     * Returns feature.
     *
     * @return FeatureNode
     */
    public function getFeature()
    {
        return $this->feature;
    }

    /**
     * Returns step node.
     *
     * @return StepNode
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * Returns current test setup.
     *
     * @return Setup
     */
    public function getSetup()
    {
        return $this->setup;
    }

    /**
     * Checks if step call, setup or teardown produced any output (stdOut or exception).
     *
     * @return Boolean
     */
    public function hasOutput()
    {
        return $this->setup->hasOutput();
    }
}
