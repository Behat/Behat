<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Hook\Call;

use Behat\Behat\Hook\Scope\StepScope;
use Behat\Gherkin\Filter\NameFilter;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Hook\Call\RuntimeFilterableHook;
use Behat\Testwork\Hook\Scope\HookScope;

/**
 * Represents a step hook.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class RuntimeStepHook extends RuntimeFilterableHook
{
    /**
     * {@inheritdoc}
     */
    public function filterMatches(HookScope $scope)
    {
        if (!$scope instanceof StepScope) {
            return false;
        }

        if (null === ($filterString = $this->getFilterString())) {
            return true;
        }

        if (!empty($filterString)) {
            $filter = new NameFilter($filterString);

            if ($filter->isFeatureMatch($scope->getFeature())) {
                return true;
            }

            return $this->isStepMatch($scope->getStep(), $filterString);
        }

        return false;
    }

    /**
     * Checks if Feature matches specified filter.
     *
     * @param StepNode $step
     * @param string   $filterString
     *
     * @return bool
     */
    private function isStepMatch(StepNode $step, $filterString)
    {
        if ('/' === $filterString[0]) {
            return 1 === preg_match($filterString, $step->getText());
        }

        return false !== mb_strpos($step->getText(), $filterString, 0, 'utf8');
    }
}
