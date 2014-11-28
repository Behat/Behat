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
use Behat\Testwork\Tester\Context\Context;
use Behat\Testwork\Tester\Context\SuiteContext;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Suite-only hook scope factory.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class SuiteScopeFactory implements ScopeFactory
{
    /**
     * {@inheritdoc}
     */
    public function createBeforeHookScope(Context $context)
    {
        switch (true) {
            case $context instanceof SuiteContext:
                return new BeforeSuiteScope($context);
        }

        throw new UnsupportedContextException(
            sprintf(
                'Can not create a Before hook scope for the context `%s`.',
                get_class($context)
            ), $context
        );
    }

    /**
     * {@inheritdoc}
     */
    public function createAfterHookScope(Context $context, TestResult $result)
    {
        switch (true) {
            case $context instanceof SuiteContext:
                return new AfterSuiteScope($context, $result);
        }

        throw new UnsupportedContextException(
            sprintf(
                'Can not create an After hook scope for the context `%s` and the result `%s`.',
                get_class($context),
                get_class($result)
            ), $context
        );
    }
}
