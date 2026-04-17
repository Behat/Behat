<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Hook\Call;

use Behat\Behat\Hook\Scope\ScenarioScope;
use Behat\Gherkin\Filter\NameFilter;
use Behat\Gherkin\Filter\TagFilter;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioInterface;
use Behat\Testwork\Hook\Call\RuntimeFilterableHook;
use Behat\Testwork\Hook\Scope\HookScope;

/**
 * Represents a scenario hook.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class RuntimeScenarioHook extends RuntimeFilterableHook
{
    public function filterMatches(HookScope $scope): bool
    {
        if (!$scope instanceof ScenarioScope) {
            return false;
        }

        if (null === ($filterString = $this->getFilterString())) {
            return true;
        }

        return $this->isMatch($scope->getFeature(), $scope->getScenario(), $filterString);
    }

    /**
     * Checks if nodes match filter.
     */
    protected function isMatch(FeatureNode $feature, ScenarioInterface $scenario, string $filterString): bool
    {
        if (str_contains($filterString, '@')) {
            return $this->isMatchTagFilter($feature, $scenario, $filterString);
        }

        if ($filterString !== '') {
            return $this->isMatchNameFilter($scenario, $filterString);
        }

        return false;
    }

    /**
     * Checks if node match tag filter.
     */
    protected function isMatchTagFilter(FeatureNode $feature, ScenarioInterface $scenario, string $filterString): bool
    {
        $filter = new TagFilter($filterString);

        return $filter->isScenarioMatch($feature, $scenario);
    }

    /**
     * Checks if scenario matches name filter.
     */
    protected function isMatchNameFilter(ScenarioInterface $scenario, string $filterString): bool
    {
        $filter = new NameFilter($filterString);

        return $filter->isScenarioMatch($scenario);
    }
}
