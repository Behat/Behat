<?php

namespace Behat\Behat\Output\Formatter;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Event\EventInterface;
use Behat\Behat\Event\ExerciseEvent;
use Behat\Behat\Output\Formatter\ProgressFormatter;

/**
 * Snippets formatter.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class SnippetsFormatter extends ProgressFormatter
{
    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            EventInterface::AFTER_EXERCISE => array('afterExercise', -50),
        );
    }

    /**
     * Returns formatter name.
     *
     * @return string
     */
    public function getName()
    {
        return 'snippets';
    }

    /**
     * Returns formatter description.
     *
     * @return string
     */
    public function getDescription()
    {
        return 'Prints only snippets for undefined steps.';
    }

    /**
     * Listens to "exercise.after" event.
     *
     * @param ExerciseEvent $event
     *
     * @uses printUndefinedStepsSnippets()
     */
    public function afterExercise(ExerciseEvent $event)
    {
        $this->writeln();
        $this->printSnippets($this->getSnippetsCollector());
    }
}
