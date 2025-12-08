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
use Behat\Gherkin\Node\ScenarioInterface;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Suite\Suite;

/**
 * Represents a BeforeScenario hook scope.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class BeforeScenarioScope implements ScenarioScope
{
    /**
     * Initializes scope.
     */
    public function __construct(
        private readonly Environment $environment,
        private readonly FeatureNode $feature,
        private readonly ScenarioInterface $scenario,
    ) {
    }

    /**
     * Returns hook scope name.
     */
    public function getName(): string
    {
        return self::BEFORE;
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
     * Returns scenario.
     */
    public function getScenario(): ScenarioInterface
    {
        return $this->scenario;
    }
}
