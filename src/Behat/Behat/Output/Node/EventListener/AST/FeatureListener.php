<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\EventListener\AST;

use Behat\Behat\EventDispatcher\Event\AfterFeatureSetup;
use Behat\Behat\EventDispatcher\Event\AfterFeatureTested;
use Behat\Behat\EventDispatcher\Event\FeatureTested;
use Behat\Behat\Output\Node\Printer\FeaturePrinter;
use Behat\Behat\Output\Node\Printer\SetupPrinter;
use Behat\Testwork\Event\Event;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\EventListener\EventListener;

/**
 * Listens to feature events and calls appropriate printers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class FeatureListener implements EventListener
{
    /**
     * @var FeaturePrinter
     */
    private $featurePrinter;
    /**
     * @var SetupPrinter
     */
    private $setupPrinter;

    /**
     * Initializes listener.
     *
     * @param FeaturePrinter $featurePrinter
     * @param SetupPrinter   $setupPrinter
     */
    public function __construct(FeaturePrinter $featurePrinter, SetupPrinter $setupPrinter)
    {
        $this->featurePrinter = $featurePrinter;
        $this->setupPrinter = $setupPrinter;
    }

    /**
     * {@inheritdoc}
     */
    public function listenEvent(Formatter $formatter, Event $event, $eventName)
    {
        if (!$event instanceof FeatureTested) {
            return;
        }

        $this->printHeaderOnBeforeEvent($formatter, $event);
        $this->printFooterOnAfterEvent($formatter, $event);
    }

    /**
     * Prints feature header on BEFORE event.
     *
     * @param Formatter $formatter
     * @param Event     $event
     */
    private function printHeaderOnBeforeEvent(Formatter $formatter, Event $event)
    {
        if (!$event instanceof AfterFeatureSetup) {
            return;
        }

        $this->setupPrinter->printSetup($formatter, $event->getSetup());
        $this->featurePrinter->printHeader($formatter, $event->getFeature());
    }

    /**
     * Prints feature footer on AFTER event.
     *
     * @param Formatter $formatter
     * @param Event     $event
     */
    private function printFooterOnAfterEvent(Formatter $formatter, Event $event)
    {
        if (!$event instanceof AfterFeatureTested) {
            return;
        }

        $this->setupPrinter->printTeardown($formatter, $event->getTeardown());
        $this->featurePrinter->printFooter($formatter, $event->getTestResult());
    }
}
