<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Hook\Scope;

use Behat\Testwork\Hook\Exception\UnsupportedContextException;
use Behat\Testwork\Tester\Context\TestContext;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Represents a factory interface for creation of hook scopes.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface ScopeFactory
{
    /**
     * Creates a `before` hook scope for the context.
     *
     * @param TestContext $context
     *
     * @return HookScope
     *
     * @throws UnsupportedContextException If provided context is unsupported by factory
     */
    public function createBeforeHookScope(TestContext $context);

    /**
     * Creates an `after` hook scope for the context and the result.
     *
     * @param TestContext    $context
     * @param TestResult $result
     *
     * @return HookScope
     *
     * @throws UnsupportedContextException If provided context is unsupported by factory
     */
    public function createAfterHookScope(TestContext $context, TestResult $result);
}
