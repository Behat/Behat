<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\EventDispatcher\Event;

use Behat\Testwork\EventDispatcher\Exception\UnsupportedContextException;
use Behat\Testwork\Tester\Context\Context;
use Behat\Testwork\Tester\Context\ExerciseContext;
use Behat\Testwork\Tester\Context\SuiteContext;
use Behat\Testwork\Tester\Result\TestResult as Result;
use Behat\Testwork\Tester\Setup\Setup;
use Behat\Testwork\Tester\Setup\Teardown;

/**
 * Exercise-based event factory.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ExerciseEventFactory implements EventFactory
{
    /**
     * {@inheritdoc}
     */
    public function createBeforeTestedEvent(Context $context)
    {
        switch (true) {
            case $context instanceof ExerciseContext:
                return new BeforeExerciseCompleted($context);

            case $context instanceof SuiteContext:
                return new BeforeSuiteTested($context);
        }

        throw new UnsupportedContextException(
            sprintf(
                'Can not create a BeforeTested event for the context `%s`.',
                get_class($context)
            ), $context
        );
    }

    /**
     * {@inheritdoc}
     */
    public function createAfterSetupEvent(Context $context, Setup $setup)
    {
        switch (true) {
            case $context instanceof ExerciseContext:
                return new AfterExerciseSetup($context, $setup);

            case $context instanceof SuiteContext:
                return new AfterSuiteSetup($context, $setup);
        }

        throw new UnsupportedContextException(
            sprintf(
                'Can not create an AfterSetup event for the context `%s` and the setup `%s`.',
                get_class($context),
                get_class($setup)
            ), $context
        );
    }

    /**
     * {@inheritdoc}
     */
    public function createBeforeTeardownEvent(Context $context, Result $result)
    {
        switch (true) {
            case $context instanceof ExerciseContext:
                return new BeforeExerciseTeardown($context, $result);

            case $context instanceof SuiteContext:
                return new BeforeSuiteTeardown($context, $result);
        }

        throw new UnsupportedContextException(
            sprintf(
                'Can not create a BeforeTeardown event for the context `%s` and the result `%s`.',
                get_class($context),
                get_class($result)
            ), $context
        );
    }

    /**
     * {@inheritdoc}
     */
    public function createAfterTestedEvent(Context $context, Result $result, Teardown $teardown)
    {
        switch (true) {
            case $context instanceof ExerciseContext:
                return new AfterExerciseCompleted($context, $result, $teardown);

            case $context instanceof SuiteContext:
                return new AfterSuiteTested($context, $result, $teardown);
        }

        throw new UnsupportedContextException(
            sprintf(
                'Can not create an AfterTested event for the context `%s` and the teardown `%s`.',
                get_class($context),
                get_class($teardown)
            ), $context
        );
    }
}
