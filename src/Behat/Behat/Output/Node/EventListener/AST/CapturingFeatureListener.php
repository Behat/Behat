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
use Behat\Behat\EventDispatcher\Event\FeatureTested;
use Behat\Behat\EventDispatcher\Event\AfterScenarioTested;
use Behat\Behat\EventDispatcher\Event\BeforeScenarioTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Behat\Output\Node\Printer\FeaturePrinter;
use Behat\Behat\Output\Node\Printer\SetupPrinter;
use Behat\Behat\Output\Node\Printer\ScenarioPrinter;
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
final class CapturingFeatureListener implements EventListener
{
    /**
     * @var FeaturePrinter
     */
    private $featureNodePrinter;
    /**
     * @var SetupPrinter
     */
    private $setupPrinter;
    /**
     * @var BeforeScenarioTested[]
     */
    private $scenarioBeforeTestedEvents = array();
    /**
     * @var AfterScenarioTested[]
     */
    private $scenarioAfterTestedEvents = array();
    /**
     * @var FeatureNode
     */
    private $feature;
    /**
     * @var BeforeStepTested[]
     */
    private $stepBeforeTestedEvents = array();
    /**
     * @var AfterStepTested[]
     */
    private $stepAfterTestedEvents = array();

    /**
     * Initializes listener.
     *
     * @param FeaturePrinter $featurePrinter
     * @param SetupPrinter   $setupPrinter
     */
    public function __construct(FeaturePrinter $featurePrinter, SetupPrinter $setupPrinter = null)
    {
        $this->featurePrinter = $featurePrinter;
        $this->setupPrinter = $setupPrinter;
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
        if ($event instanceof BeforeScenarioTested) {
            $this->scenarioBeforeTestedEvents[$event->getScenario()->getTitle()] = $event;
        } else {
            $this->scenarioAfterTestedEvents[$event->getScenario()->getTitle()] = $event;
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

        $this->featureBeforeTestedEvent = $event->getFeature();
    }

    /**
     * Captures step tested event.
     *
     * @param StepTested $event
     */
    private function captureStepEvent(StepTested $event)
    {
        if ($event instanceof BeforeStepTested) {
            $this->stepBeforeTestedEvents[$event->getStep()->getLine()] = $event;
        } else {
            $this->stepAfterTestedEvents[$event->getStep()->getLine()] = $event;
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

        $this->featurePrinter->printHeader($formatter, $this->featureBeforeTestedEvent);
    }
}
