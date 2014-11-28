<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\EventDispatcher\Event;

use Behat\Behat\Tester\Context\StepContext;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepNode;
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
     * @param StepContext $context
     * @param Setup       $setup
     */
    public function __construct(StepContext $context, Setup $setup)
    {
        parent::__construct($context->getEnvironment());

        $this->feature = $context->getFeature();
        $this->step = $context->getStep();
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
