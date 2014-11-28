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
use Behat\Testwork\EventDispatcher\Event\BeforeTeardown;
use Behat\Testwork\Tester\Context\SpecificationContext;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Represents an event right before feature is teardown.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class BeforeFeatureTeardown extends FeatureTested implements BeforeTeardown
{
    /**
     * @var FeatureNode
     */
    private $feature;
    /**
     * @var TestResult
     */
    private $result;

    /**
     * Initializes event.
     *
     * @param SpecificationContext $context
     * @param TestResult           $result
     */
    public function __construct(SpecificationContext $context, TestResult $result)
    {
        parent::__construct($context->getEnvironment());

        $this->feature = $context->getSpecification();
        $this->result = $result;
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
     * Returns current test result.
     *
     * @return TestResult
     */
    public function getTestResult()
    {
        return $this->result;
    }
}
