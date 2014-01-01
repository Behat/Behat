<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester\Event;

use Behat\Behat\Tester\Result\OutlineTestResult;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\OutlineNode;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Hook\Event\LifecycleEvent;
use Behat\Testwork\Suite\Suite;

/**
 * Outline tested event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class OutlineTested extends LifecycleEvent
{
    const BEFORE = 'tester.outline_tested.before';
    const AFTER = 'tester.outline_tested.after';
    /**
     * @var OutlineNode
     */
    private $outline;
    /**
     * @var null|OutlineTestResult
     */
    private $testResult;

    /**
     * Initializes event.
     *
     * @param Suite                  $suite
     * @param Environment            $environment
     * @param FeatureNode            $feature
     * @param OutlineNode            $outline
     * @param null|OutlineTestResult $testResult
     */
    public function __construct(
        Suite $suite,
        Environment $environment,
        FeatureNode $feature,
        OutlineNode $outline,
        OutlineTestResult $testResult = null
    ) {
        parent::__construct($suite, $environment, $feature);

        $this->outline = $outline;
        $this->testResult = $testResult;
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
     * @return null|OutlineTestResult
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
        return $this->testResult->getResultCode();
    }
}
