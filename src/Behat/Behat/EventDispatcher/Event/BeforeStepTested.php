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
use Behat\Testwork\EventDispatcher\Event\BeforeTested;

/**
 * Represents an event before step test.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class BeforeStepTested extends StepTested implements BeforeTested
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
     * Initializes event.
     *
     * @param StepContext $context
     */
    public function __construct(StepContext $context)
    {
        parent::__construct($context->getEnvironment());

        $this->feature = $context->getFeature();
        $this->step = $context->getStep();
    }

    /**
     * {@inheritdoc}
     */
    public function getEventName()
    {
        return self::BEFORE;
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
}
