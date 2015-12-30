<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Hook\Scope;

use Behat\Behat\Tester\Context\StepContext;
use Behat\Behat\Tester\Result\StepResult;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Hook\Scope\AfterTestScope;
use Behat\Testwork\Suite\Suite;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Represents an AfterStep hook scope.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class AfterStepScope implements StepScope, AfterTestScope
{
    /**
     * @var FeatureNode
     */
    private $feature;
    /**
     * @var StepNode
     */
    private $step;
    /**
     * @var Environment
     */
    private $environment;
    /**
     * @var StepResult
     */
    private $result;

    /**
     * Initializes scope.
     *
     * @param StepContext $context
     * @param StepResult  $result
     */
    public function __construct(StepContext $context, StepResult $result)
    {
        $this->feature = $context->getFeature();
        $this->step = $context->getStep();
        $this->environment = $context->getEnvironment();
        $this->result = $result;
    }

    /**
     * Returns hook scope name.
     *
     * @return string
     */
    public function getName()
    {
        return self::AFTER;
    }

    /**
     * Returns hook suite.
     *
     * @return Suite
     */
    public function getSuite()
    {
        return $this->environment->getSuite();
    }

    /**
     * Returns hook environment.
     *
     * @return Environment
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Returns scope feature.
     *
     * @return FeatureNode
     */
    public function getFeature()
    {
        return $this->feature;
    }

    /**
     * Returns scope step.
     *
     * @return StepNode
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * Returns test result.
     *
     * @return TestResult
     */
    public function getTestResult()
    {
        return $this->result;
    }
}
