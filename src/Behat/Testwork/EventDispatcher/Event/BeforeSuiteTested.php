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

/**
 * Represents an event in which suite is prepared to be tested.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class BeforeSuiteTested extends SuiteTested implements BeforeTested
{
    /**
     * @var SpecificationIterator
     */
    private $iterator;

    /**
     * Initializes event.
     *
     * @param SuiteContext $context
     */
    public function __construct(SuiteContext $context)
    {
        parent::__construct($context->getEnvironment());

        $this->iterator = $context->getSpecificationIterator();
    }

    /**
     * {@inheritdoc}
     */
    public function getEventName()
    {
        return self::BEFORE;
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
}
