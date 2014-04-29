<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\EventDispatcher\Event;

use Behat\Testwork\Specification\SpecificationIterator;
use Symfony\Component\EventDispatcher\Event;

/**
 * Represents an exercise event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class ExerciseCompleted extends Event
{
    const BEFORE = 'tester.exercise_completed.before';
    const AFTER_SETUP = 'tester.exercise_completed.after_setup';
    const BEFORE_TEARDOWN = 'tester.exercise_completed.before_teardown';
    const AFTER = 'tester.exercise_completed.after';

    /**
     * Returns specification iterators.
     *
     * @return SpecificationIterator[]
     */
    abstract public function getSpecificationIterators();
}
