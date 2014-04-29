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
use Behat\Testwork\EventDispatcher\Event\AfterTested;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Setup\Teardown;

/**
 * Represents an event after outline was tested.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class AfterOutlineTested extends OutlineTested implements AfterTested
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
     * @var TestResult
     */
    private $result;
    /**
     * @var Teardown
     */
    private $teardown;

    /**
     * Initializes event.
     *
     * @param Environment $env
     * @param FeatureNode $feature
     * @param OutlineNode $outline
     * @param TestResult  $result
     * @param Teardown    $teardown
     */
    public function __construct(
        Environment $env,
        FeatureNode $feature,
        OutlineNode $outline,
        TestResult $result,
        Teardown $teardown
    ) {
        parent::__construct($env);

        $this->feature = $feature;
        $this->outline = $outline;
        $this->result = $result;
        $this->teardown = $teardown;
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
     * Returns current test result.
     *
     * @return TestResult
     */
    public function getTestResult()
    {
        return $this->result;
    }

    /**
     * Returns current test teardown.
     *
     * @return Teardown
     */
    public function getTeardown()
    {
        return $this->teardown;
    }
}
