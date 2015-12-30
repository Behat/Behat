<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\EventDispatcher\Event;

use Behat\Testwork\Specification\GroupedSpecificationIterator;
use Behat\Testwork\Tester\Context\ExerciseContext;
use Behat\Testwork\Tester\Setup\Setup;

/**
 * Represents an event in which exercise is prepared to be executed.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class AfterExerciseSetup extends ExerciseCompleted implements AfterSetup
{
    /**
     * @var GroupedSpecificationIterator[]
     */
    private $specificationIterators;
    /**
     * @var Setup
     */
    private $setup;

    /**
     * Initializes event.
     *
     * @param ExerciseContext $context
     * @param Setup           $setup
     */
    public function __construct(ExerciseContext $context, Setup $setup)
    {
        $this->specificationIterators = $context->getGroupedSpecificationIterators();
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
     * Returns specification iterators.
     *
     * @return GroupedSpecificationIterator[]
     */
    public function getSpecificationIterators()
    {
        return $this->specificationIterators;
    }

    /**
     * Returns exercise setup result.
     *
     * @return Setup
     */
    public function getSetup()
    {
        return $this->setup;
    }
}
