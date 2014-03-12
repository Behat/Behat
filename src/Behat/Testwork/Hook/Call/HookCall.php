<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Hook\Call;

use Behat\Testwork\Environment\Call\EnvironmentCall;
use Behat\Testwork\Hook\Hook;
use Behat\Testwork\Hook\Scope\HookScope;

/**
 * Represents a hook call.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class HookCall extends EnvironmentCall
{
    /**
     * @var HookScope
     */
    private $scope;

    /**
     * Initializes hook call.
     *
     * @param HookScope    $scope
     * @param Hook         $hook
     * @param null|integer $errorReportingLevel
     */
    public function __construct(HookScope $scope, Hook $hook, $errorReportingLevel = null)
    {
        parent::__construct($scope->getEnvironment(), $hook, array($scope), $errorReportingLevel);

        $this->scope = $scope;
    }

    /**
     * Returns hook scope.
     *
     * @return HookScope
     */
    public function getScope()
    {
        return $this->scope;
    }
}
