<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Hook;

use Behat\Testwork\Call\Callee;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Environment\EnvironmentManager;
use Behat\Testwork\Hook\Scope\HookScope;

/**
 * Finds hooks using provided environments or scopes.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class HookRepository
{
    /**
     * Initializes repository.
     */
    public function __construct(
        private readonly EnvironmentManager $environmentManager,
    ) {
    }

    /**
     * Returns all available hooks for a specific environment.
     *
     * @return Hook[]
     */
    public function getEnvironmentHooks(Environment $environment)
    {
        return array_filter(
            $this->environmentManager->readEnvironmentCallees($environment),
            fn (Callee $callee): bool => $callee instanceof Hook
        );
    }

    /**
     * Returns hooks for a specific event.
     *
     * @return Hook[]
     */
    public function getScopeHooks(HookScope $scope)
    {
        return array_filter(
            $this->getEnvironmentHooks($scope->getEnvironment()),
            function (Hook $hook) use ($scope) {
                if ($scope->getName() !== $hook->getScopeName()) {
                    return false;
                }

                return !($hook instanceof FilterableHook && !$hook->filterMatches($scope));
            }
        );
    }
}
