<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Hook\Scope;

use Behat\Testwork\Specification\SpecificationIterator;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Tester\Context\SuiteContext;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Represents a scope for AfterSuite hook.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class AfterSuiteScope implements SuiteScope, AfterTestScope
{
    /**
     * @var Environment
     */
    private $environment;
    /**
     * @var SpecificationIterator
     */
    private $iterator;
    /**
     * @var TestResult
     */
    private $result;

    /**
     * Initializes scope.
     *
     * @param SuiteContext $context
     * @param TestResult   $result
     */
    public function __construct(SuiteContext $context, TestResult $result)
    {
        $this->iterator = $context->getSpecificationIterator();
        $this->environment = $context->getEnvironment();
        $this->result = $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::AFTER;
    }

    /**
     * {@inheritdoc}
     */
    public function getSuite()
    {
        return $this->environment->getSuite();
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * {@inheritdoc}
     */
    public function getSpecificationIterator()
    {
        return $this->iterator;
    }

    /**
     * {@inheritdoc}
     */
    public function getTestResult()
    {
        return $this->result;
    }
}
