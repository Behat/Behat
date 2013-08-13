<?php

namespace Behat\Behat\Event;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Symfony\Component\EventDispatcher\Event;

/**
 * Exercise event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ExerciseEvent extends Event implements EventInterface
{
    /**
     * @var Boolean
     */
    private $completed;

    /**
     * Initializes event.
     *
     * @param Boolean $completed
     */
    public function __construct($completed = false)
    {
        $this->completed = (bool)$completed;
    }

    /**
     * Checks whether project was completed entirely.
     *
     * @return Boolean
     */
    public function isCompleted()
    {
        return $this->completed;
    }
}
