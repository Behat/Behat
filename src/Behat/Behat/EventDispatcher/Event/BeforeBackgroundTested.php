<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\EventDispatcher\Event;

use Behat\Behat\Tester\Context\BackgroundContext;
use Behat\Gherkin\Node\BackgroundNode;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioInterface;
use Behat\Testwork\EventDispatcher\Event\BeforeTested;

/**
 * Represents a BeforeBackgroundTested event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class BeforeBackgroundTested extends BackgroundTested implements BeforeTested
{
    /**
     * @var FeatureNode
     */
    private $feature;
    /**
     * @var BackgroundNode
     */
    private $background;

    /**
     * Initializes event.
     *
     * @param BackgroundContext $context
     */
    public function __construct(BackgroundContext $context)
    {
        parent::__construct($context->getEnvironment());

        $this->feature = $context->getFeature();
        $this->background = $context->getBackground();
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
     * Returns scenario node.
     *
     * @return ScenarioInterface
     */
    public function getScenario()
    {
        return $this->background;
    }

    /**
     * Returns background node.
     *
     * @return BackgroundNode
     */
    public function getBackground()
    {
        return $this->background;
    }
}
