<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\EventDispatcher\Event;

use Behat\Behat\Tester\Context\ScenarioContext;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\OutlineNode;
use Behat\Testwork\EventDispatcher\Event\AfterSetup;
use Behat\Testwork\Tester\Setup\Setup;

/**
 * Represents an event right after outline setup.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class AfterOutlineSetup extends OutlineTested implements AfterSetup
{
    /**
     * @var FeatureNode
     */
    private $feature;
    /**
     * @var OutlineNode
     */
    private $outline;
    /**
     * @var Setup
     */
    private $setup;

    /**
     * Initializes event.
     *
     * @param ScenarioContext $context
     * @param Setup           $setup
     */
    public function __construct(ScenarioContext $context, Setup $setup)
    {
        parent::__construct($context->getEnvironment());

        $this->feature = $context->getFeature();
        $this->outline = $context->getScenario();
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
     * Returns outline node.
     *
     * @return OutlineNode
     */
    public function getOutline()
    {
        return $this->outline;
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
