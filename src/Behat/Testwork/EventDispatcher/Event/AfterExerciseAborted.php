<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\EventDispatcher\Event;

/**
 * Represents an event in which exercise was aborted.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class AfterExerciseAborted extends ExerciseCompleted
{
    /**
     * {@inheritdoc}
     */
    public function getSpecificationIterators()
    {
        return array();
    }
}
