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
use Behat\Testwork\EventDispatcher\Event\AfterSetup;
use Behat\Testwork\Tester\Setup\Setup;

/**
 * Represents an event right after background was setup for testing.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class AfterBackgroundSetup extends BackgroundTested implements AfterSetup
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
     * @var Setup
     */
    private $setup;

    /**
     * Initializes event.
     *
     * @param BackgroundContext $context
     * @param Setup             $setup
     */
    public function __construct(BackgroundContext $context, Setup $setup)
    {
        parent::__construct($context->getEnvironment());

        $this->feature = $context->getFeature();
        $this->background = $context->getBackground();
        $this->setup = $setup;
    }

    /**
     * {@inheritdoc}
     */
    public function getEventName()
    {
        return self::AFTER_SETUP;
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

    /**
     * Returns current test setup.
     *
     * @return Setup
     */
    public function getSetup()
    {
        return $this->setup;
    }
}
