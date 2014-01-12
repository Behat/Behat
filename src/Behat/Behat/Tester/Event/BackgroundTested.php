<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester\Event;

use Behat\Gherkin\Node\BackgroundNode;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Hook\Event\LifecycleEvent;
use Behat\Testwork\Tester\Result\TestResults;

/**
 * Background tested event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class BackgroundTested extends LifecycleEvent
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
    private $stepTestResults;

    /**
     * Initializes event.
     *
     * @param Environment      $environment
     * @param FeatureNode      $feature
     * @param BackgroundNode   $background
     * @param null|TestResults $stepTestResult
     */
    public function __construct(
        Environment $environment,
        FeatureNode $feature,
        BackgroundNode $background,
        TestResults $stepTestResult = null
    ) {
        parent::__construct($environment);

        $this->feature = $feature;
        $this->background = $background;
        $this->stepTestResults = $stepTestResult;
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
    public function getStepTestResults()
    {
        return $this->stepTestResults;
    }

    /**
     * Returns step tester result status.
     *
     * @return integer
     */
    public function getResultCode()
    {
        return $this->stepTestResults->getResultCode();
    }
}
