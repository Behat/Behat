<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\EventListener\AST;

use Behat\Behat\EventDispatcher\Event\AfterFeatureTested;
use Behat\Behat\EventDispatcher\Event\BeforeFeatureTested;
use Behat\Behat\EventDispatcher\Event\AfterScenarioTested;
use Behat\Behat\EventDispatcher\Event\AfterStepTested;
use Behat\Behat\EventDispatcher\Event\StepTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Behat\Output\Node\Printer\FeaturePrinter;
use Behat\Behat\Output\Node\Printer\ScenarioElementPrinter;
use Behat\Behat\Output\Node\Printer\StepPrinter;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\EventListener\EventListener;
use Symfony\Component\EventDispatcher\Event;

/**
 * Listens to feature, scenario and step events and calls appropriate printers.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
final class FeatureElementListener implements EventListener
{
    /**
     * @var FeaturePrinter
     */
    private $featurePrinter;
    /**
     * @var ScenarioElementPrinter
     */
    private $scenarioPrinter;
    /**
     * @var StepPrinter
     */
    private $stepPrinter;
    /**
     * @var FeatureNode
     */
    private $beforeFeatureTestedEvent;
    /**
     * @var AfterScenarioTested[]
     */
    private $afterScenarioTestedEvents = array();
    /**
     * @var AfterStepTested[]
     */
    private $afterStepTestedEvents = array();

    /**
     * Initializes listener.
     *
     * @param FeaturePrinter $featurePrinter
     * @param ScenarioElementPrinter $scenarioPrinter
     * @param \Behat\Behat\Output\Node\Printer\StepPrinter $stepPrinter
     */
    public function __construct(FeaturePrinter $featurePrinter, ScenarioElementPrinter $scenarioPrinter, StepPrinter $stepPrinter)
    {
        $this->featurePrinter = $featurePrinter;
        $this->scenarioPrinter = $scenarioPrinter;
        $this->stepPrinter = $stepPrinter;
    }

    /**
     * {@inheritdoc}
     */
    public function listenEvent(Formatter $formatter, Event $event, $eventName)
    {
        if ($event instanceof ScenarioTested) {
            $this->captureScenarioEvent($event);
        }

        if ($event instanceof StepTested) {
            $this->captureStepEvent($event);
        }

        $this->captureFeatureOnBeforeEvent($event);
        $this->printFeatureOnAfterEvent($formatter, $event);
    }

    /**
     * Captures scenario tested event.
     *
     * @param ScenarioTested $event
     */
    private function captureScenarioEvent(ScenarioTested $event)
    {
        if ($event instanceof AfterScenarioTested) {
            $this->afterScenarioTestedEvents[$event->getScenario()->getTitle()] = array(
                'event' => $event,
                'step_events' => $this->afterStepTestedEvents,
            );

            $this->afterStepTestedEvents = array();
        }
    }

    /**
     * Captures feature on BEFORE event.
     *
     * @param Event $event
     */
    private function captureFeatureOnBeforeEvent(Event $event)
    {
        if (!$event instanceof BeforeFeatureTested) {
            return;
        }

        $this->beforeFeatureTestedEvent = $event->getFeature();
    }

    /**
     * Captures step tested event.
     *
     * @param StepTested $event
     */
    private function captureStepEvent(StepTested $event)
    {
        if ($event instanceof AfterStepTested) {
            $this->afterStepTestedEvents[$event->getStep()->getLine()] = $event;
        }
    }

    /**
     * Prints the feature on AFTER event.
     *
     * @param Formatter $formatter
     * @param Event     $event
     */
    public function printFeatureOnAfterEvent(Formatter $formatter, Event $event)
    {
        if (!$event instanceof AfterFeatureTested) {
            return;
        }

        $this->featurePrinter->printHeader($formatter, $this->beforeFeatureTestedEvent);

        foreach ($this->afterScenarioTestedEvents as $afterScenario) {
            $afterScenarioTested = $afterScenario['event'];
            $this->scenarioPrinter->printOpenTag($formatter, $afterScenarioTested->getFeature(), $afterScenarioTested->getScenario(), $afterScenarioTested->getTestResult());

            foreach ($afterScenario['step_events'] as $afterStepTested) {
                $this->stepPrinter->printStep($formatter, $afterScenarioTested->getScenario(), $afterStepTested->getStep(), $afterStepTested->getTestResult());
            }

            $this->scenarioPrinter->printCloseTag($formatter);
        }

        $this->featurePrinter->printFooter($formatter, $event->getTestResult());
    }
}
