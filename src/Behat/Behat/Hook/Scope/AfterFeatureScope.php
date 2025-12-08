<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Hook\Scope;

use Behat\Gherkin\Node\FeatureNode;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Hook\Scope\AfterTestScope;
use Behat\Testwork\Suite\Suite;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Represents an AfterFeature hook scope.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class AfterFeatureScope implements FeatureScope, AfterTestScope
{
    /**
     * Initializes scope.
     */
    public function __construct(
        private readonly Environment $environment,
        private readonly FeatureNode $feature,
        private readonly TestResult $result,
    ) {
    }

    /**
     * Returns hook scope name.
     */
    public function getName(): string
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
     */
    public function getEnvironment(): Environment
    {
        return $this->environment;
    }

    /**
     * Returns scope feature.
     */
    public function getFeature(): FeatureNode
    {
        return $this->feature;
    }

    /**
     * Returns test result.
     */
    public function getTestResult(): TestResult
    {
        return $this->result;
    }
}
