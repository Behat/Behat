<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\EventDispatcher\Event;

use Behat\Testwork\Specification\SpecificationIterator;
use Behat\Testwork\Tester\Context\SuiteContext;
use Behat\Testwork\Tester\Setup\Setup;

/**
 * Represents an event right after a suite setup.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class AfterSuiteSetup extends SuiteTested implements AfterSetup
{
    /**
     * @var SpecificationIterator
     */
    private $iterator;
    /**
     * @var Setup
     */
    private $setup;

    /**
     * Initializes event.
     *
     * @param SuiteContext $context
     * @param Setup        $setup
     */
    public function __construct(SuiteContext $context, Setup $setup)
    {
        parent::__construct($context->getEnvironment());

        $this->iterator = $context->getSpecificationIterator();
        $this->setup = $setup;
    }

    /**
     * {@inheritdoc}
     */
    public function getEventName()
    {
        return self::AFTER_SETUP;
    }

    /**
     * Returns specification iterator.
     *
     * @return SpecificationIterator
     */
    public function getSpecificationIterator()
    {
        return $this->iterator;
    }

    /**
     * Returns current test setup.
     *
     * @return Setup
     */
    public function getSetup()
    {
        return $this->setup;
    }
}
