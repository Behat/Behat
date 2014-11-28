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
use Behat\Testwork\EventDispatcher\Event\BeforeTested;

/**
 * Represents an event before outline is tested.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class BeforeOutlineTested extends OutlineTested implements BeforeTested
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
     * Initializes event.
     *
     * @param ScenarioContext $context
     */
    public function __construct(ScenarioContext $context)
    {
        parent::__construct($context->getEnvironment());

        $this->feature = $context->getFeature();
        $this->outline = $context->getScenario();
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
     * Returns outline node.
     *
     * @return OutlineNode
     */
    public function getOutline()
    {
        return $this->outline;
    }
}
