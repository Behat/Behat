<?php

namespace Behat\Behat\Formatter;

use Symfony\Component\EventDispatcher\EventDispatcher;

use Behat\Behat\Event\SuiteEvent;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Snippets formatter.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class SnippetsFormatter extends ProgressFormatter
{
    /**
     * {@inheritdoc}
     */
    public static function getDescription()
    {
        return "Prints only snippets for undefined steps.";
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultParameters()
    {
        return array();
    }

    /**
     * @see     Symfony\Component\EventDispatcher\EventSubscriberInterface::getSubscribedEvents()
     */
    public static function getSubscribedEvents()
    {
        return array('afterSuite' => 'afterSuite');
    }

    /**
     * Listens to "suite.after" event.
     *
     * @param   Behat\Behat\Event\SuiteEvent    $event
     *
     * @uses    printUndefinedStepsSnippets()
     */
    public function afterSuite(SuiteEvent $event)
    {
        $logger = $event->getLogger();

        $this->writeln();
        $this->printSnippets($logger);
    }
}
