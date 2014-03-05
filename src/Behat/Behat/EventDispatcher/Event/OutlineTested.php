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
use Behat\Gherkin\Node\OutlineNode;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\EventDispatcher\Event\LifecycleEvent;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Behat outline tested event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class OutlineTested extends LifecycleEvent implements GherkinNodeTested
{
    const BEFORE = 'tester.outline_tested.before';
    const AFTER = 'tester.outline_tested.after';

    /**
     * @var FeatureNode
     */
    private $feature;
    /**
     * @var OutlineNode
     */
    private $outline;
    /**
     * @var null|TestResult
     */
    private $testResult;

    /**
     * Initializes event.
     *
     * @param Environment     $environment
     * @param FeatureNode     $feature
     * @param OutlineNode     $outline
     * @param null|TestResult $testResults
     */
    public function __construct(
        Environment $environment,
        FeatureNode $feature,
        OutlineNode $outline,
        TestResult $testResults = null
    ) {
        parent::__construct($environment);

        $this->feature = $feature;
        $this->outline = $outline;
        $this->testResult = $testResults;
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
     * Returns outline examples test results.
     *
     * @return null|TestResult
     */
    public function getTestResult()
    {
        return $this->testResult;
    }

    /**
     * Returns step tester result status.
     *
     * @return integer
     */
    public function getResultCode()
    {
        if (null === $this->testResult) {
            return null;
        }

        return $this->testResult->getResultCode();
    }

    /**
     * {@inheritdoc}
     */
    public function getNode()
    {
        return $this->getOutline();
    }
}
