<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\EventListener\AST;

use Behat\Behat\EventDispatcher\Event\FeatureTested;
use Behat\Behat\Output\Node\Printer\FeaturePrinter;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\EventListener\EventListener;
use Symfony\Component\EventDispatcher\Event;

/**
 * Behat pretty feature listener.
 *
 * Listens to feature events and calls appropriate printers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FeatureListener implements EventListener
{
    /**
     * @var FeaturePrinter
     */
    private $featurePrinter;

    /**
     * Initializes listener.
     *
     * @param FeaturePrinter $featurePrinter
     */
    public function __construct(FeaturePrinter $featurePrinter)
    {
        $this->featurePrinter = $featurePrinter;
    }

    /**
     * {@inheritdoc}
     */
    public function listenEvent(Formatter $formatter, Event $event, $eventName)
    {
        if (!$event instanceof FeatureTested) {
            return;
        }

        $this->printHeaderOnBeforeEvent($formatter, $event, $eventName);
        $this->printFooterOnAfterEvent($formatter, $event, $eventName);
    }

    /**
     * Prints feature header on BEFORE event.
     *
     * @param Formatter $formatter
     * @param Event     $event
     * @param string    $eventName
     */
    private function printHeaderOnBeforeEvent(Formatter $formatter, Event $event, $eventName)
    {
        if (!$event instanceof FeatureTested || FeatureTested::BEFORE !== $eventName) {
            return;
        }

        $this->featurePrinter->printHeader($formatter, $event->getFeature());
    }

    /**
     * Prints feature footer on AFTER event.
     *
     * @param Formatter $formatter
     * @param Event     $event
     * @param string    $eventName
     */
    private function printFooterOnAfterEvent(Formatter $formatter, Event $event, $eventName)
    {
        if (!$event instanceof FeatureTested || FeatureTested::AFTER !== $eventName) {
            return;
        }

        $this->featurePrinter->printFooter($formatter, $event->getFeature(), $event->getTestResult());
    }
}
