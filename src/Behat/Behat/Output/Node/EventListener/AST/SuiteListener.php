<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\EventListener\AST;

use Behat\Behat\Output\Node\Printer\SetupPrinter;
use Behat\Testwork\EventDispatcher\Event\AfterSuiteSetup;
use Behat\Testwork\EventDispatcher\Event\AfterSuiteTested;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\EventListener\EventListener;
use Symfony\Component\EventDispatcher\Event;

/**
 * Behat suite listener.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class SuiteListener implements EventListener
{
    /**
     * @var SetupPrinter
     */
    private $setupPrinter;

    /**
     * Initializes listener.
     *
     * @param SetupPrinter $setupPrinter
     */
    public function __construct(SetupPrinter $setupPrinter)
    {
        $this->setupPrinter = $setupPrinter;
    }

    /**
     * {@inheritdoc}
     */
    public function listenEvent(Formatter $formatter, Event $event, $eventName)
    {
        if ($event instanceof AfterSuiteSetup) {
            $this->setupPrinter->printSetup($formatter, $event->getSetup());
        }

        if ($event instanceof AfterSuiteTested) {
            $this->setupPrinter->printTeardown($formatter, $event->getTeardown());
        }
    }
}
