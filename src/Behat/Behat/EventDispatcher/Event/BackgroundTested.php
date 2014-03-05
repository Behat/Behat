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
use Behat\Testwork\EventDispatcher\Event\LifecycleEvent;
use Behat\Testwork\Tester\Result\TestResults;

/**
 * Behat background tested event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class BackgroundTested extends LifecycleEvent implements ScenarioLikeTested
{
    const BEFORE = 'tester.background_tested.before';
    const AFTER = 'tester.background_tested.after';

    /**
     * @var FeatureNode
     */
    private $feature;
    /**
     * @var BackgroundNode
     */
    private $background;
    /**
     * @var null|TestResults
     */
    private $testResults;

    /**
     * Initializes event.
     *
     * @param Environment      $environment
     * @param FeatureNode      $feature
     * @param BackgroundNode   $background
     * @param null|TestResults $testResults
     */
    public function __construct(
        Environment $environment,
        FeatureNode $feature,
        BackgroundNode $background,
        TestResults $testResults = null
    ) {
        parent::__construct($environment);

        $this->feature = $feature;
        $this->background = $background;
        $this->testResults = $testResults;
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
     * Returns collection of background steps tests results.
     *
     * @return null|TestResults
     */
    public function getTestResult()
    {
        return $this->testResults;
    }

    /**
     * Returns step tester result status.
     *
     * @return integer
     */
    public function getResultCode()
    {
        if (null === $this->testResults) {
            return null;
        }

        return $this->testResults->getResultCode();
    }

    /**
     * {@inheritdoc}
     */
    public function getNode()
    {
        return $this->getBackground();
    }
}
