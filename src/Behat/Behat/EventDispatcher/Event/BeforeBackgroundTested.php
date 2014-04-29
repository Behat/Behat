<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\EventDispatcher\Event;

use Behat\Gherkin\Node\BackgroundNode;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioInterface;
use Behat\Testwork\Environment\Environment;
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
     * @param Environment    $env
     * @param FeatureNode    $feature
     * @param BackgroundNode $background
     */
    public function __construct(Environment $env, FeatureNode $feature, BackgroundNode $background)
    {
        parent::__construct($env);

        $this->feature = $feature;
        $this->background = $background;
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
