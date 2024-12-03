<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Hook\Call;

use Behat\Behat\Hook\Scope\FeatureScope;
use Behat\Gherkin\Filter\NameFilter;
use Behat\Gherkin\Filter\TagFilter;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Testwork\Call\Exception\BadCallbackException;
use Behat\Testwork\Hook\Call\RuntimeFilterableHook;
use Behat\Testwork\Hook\Scope\HookScope;
use ReflectionFunction;
use ReflectionMethod;

/**
 * Represents a feature hook.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class RuntimeFeatureHook extends RuntimeFilterableHook
{
    /**
     * Initializes hook.
     *
     * @param string      $scopeName
     * @param null|string $filterString
     * @param callable|array $callable
     * @param null|string $description
     *
     * @throws BadCallbackException If callback is method, but not a static one
     */
    public function __construct($scopeName, $filterString, $callable, $description = null)
    {
        parent::__construct($scopeName, $filterString, $callable, $description);

        if ($this->isAnInstanceMethod()) {
            if (is_array($callable)) {
                $className = $callable[0];
                $methodName = $callable[1];
            } else {
                $reflection = new ReflectionMethod($callable);
                $className = $reflection->getDeclaringClass()->getShortName();
                $methodName = $reflection->getName();
            }

            throw new BadCallbackException(sprintf(
                'Feature hook callback: %s::%s() must be a static method',
                $className,
                $methodName
            ), $callable);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function filterMatches(HookScope $scope)
    {
        if (!$scope instanceof FeatureScope) {
            return false;
        }

        if (null === ($filterString = $this->getFilterString())) {
            return true;
        }

        return $this->isMatch($scope->getFeature(), $filterString);
    }

    /**
     * @param FeatureNode $feature
     * @param string      $filterString
     *
     * @return bool
     */
    private function isMatch(FeatureNode $feature, $filterString)
    {
        if (false !== strpos($filterString, '@')) {
            return $this->isMatchTagFilter($feature, $filterString);
        }

        if (!empty($filterString)) {
            return $this->isMatchNameFilter($feature, $filterString);
        }

        return false;
    }

    /**
     * Checks if feature matches tag filter.
     *
     * @param FeatureNode $feature
     * @param string      $filterString
     *
     * @return bool
     */
    private function isMatchTagFilter(FeatureNode $feature, $filterString)
    {
        $filter = new TagFilter($filterString);

        return $filter->isFeatureMatch($feature);
    }

    /**
     * Checks if feature matches name filter.
     *
     * @param FeatureNode $feature
     * @param string      $filterString
     *
     * @return bool
     */
    private function isMatchNameFilter(FeatureNode $feature, $filterString)
    {
        $filter = new NameFilter($filterString);

        return $filter->isFeatureMatch($feature);
    }
}
