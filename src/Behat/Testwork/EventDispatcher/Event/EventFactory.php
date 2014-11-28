<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\EventDispatcher\Event;

use Behat\Testwork\Tester\Context\Context;
use Behat\Testwork\Tester\Result\TestResult as Result;
use Behat\Testwork\Tester\Setup\Setup;
use Behat\Testwork\Tester\Setup\Teardown;

/**
 * Represents a factory interface for events creation.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface EventFactory
{
    /**
     * Creates a `beforeTested` event just before the testing starts.
     *
     * @param Context $context
     *
     * @return BeforeTested|Event
     */
    public function createBeforeTestedEvent(Context $context);

    /**
     * Creates an `afterSetup` event just after the setUp, but before actual testing.
     *
     * @param Context $context
     * @param Setup   $setup
     *
     * @return AfterSetup|Event
     */
    public function createAfterSetupEvent(Context $context, Setup $setup);

    /**
     * Creates a `beforeTeardown` event after testing happened, but before tearDown commenced.
     *
     * @param Context $context
     * @param Result  $result
     *
     * @return BeforeTeardown|Event
     */
    public function createBeforeTeardownEvent(Context $context, Result $result);

    /**
     * Creates an `afterTested` event after all testing routines finished.
     *
     * @param Context  $context
     * @param Result   $result
     * @param Teardown $teardown
     *
     * @return AfterTested|Event
     */
    public function createAfterTestedEvent(Context $context, Result $result, Teardown $teardown);
}
