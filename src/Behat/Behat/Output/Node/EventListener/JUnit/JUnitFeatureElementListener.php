<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\EventListener\JUnit;

use Behat\Behat\EventDispatcher\Event\AfterFeatureTested;
use Behat\Behat\EventDispatcher\Event\AfterScenarioTested;
use Behat\Behat\EventDispatcher\Event\AfterStepSetup;
use Behat\Behat\EventDispatcher\Event\AfterStepTested;
use Behat\Behat\EventDispatcher\Event\BeforeFeatureTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Behat\Output\Node\Printer\FeaturePrinter;
use Behat\Behat\Output\Node\Printer\JUnit\JUnitScenarioPrinter;
use Behat\Behat\Output\Node\Printer\SetupPrinter;
use Behat\Behat\Output\Node\Printer\StepPrinter;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Testwork\Event\Event;
use Behat\Testwork\EventDispatcher\Event\AfterSetup;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\EventListener\EventListener;

/**
 * Listens to feature, scenario and step events and calls appropriate printers.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
final class JUnitFeatureElementListener implements EventListener
{
    /**
     * @var FeaturePrinter
     */
    private $featurePrinter;
    /**
     * @var JUnitScenarioPrinter
     */
    private $scenarioPrinter;
    /**
     * @var StepPrinter
     */
    private $stepPrinter;
    /**
     * @var SetupPrinter
     */
    private $setupPrinter;
    /**
     * @var AfterStepTested[]
     */
    private $afterStepTestedEvents = array();
    /**
     * @var AfterStepSetup[]
     */
    private $afterStepSetupEvents = array();

    /**
     * Initializes listener.
     *
     * @param FeaturePrinter $featurePrinter
     * @param JUnitScenarioPrinter $scenarioPrinter
     * @param StepPrinter $stepPrinter
     * @param SetupPrinter $setupPrinter
     */
    public function __construct(FeaturePrinter $featurePrinter,
                                JUnitScenarioPrinter $scenarioPrinter,
                                StepPrinter $stepPrinter,
                                SetupPrinter $setupPrinter)
    {
        $this->featurePrinter = $featurePrinter;
        $this->scenarioPrinter = $scenarioPrinter;
        $this->stepPrinter = $stepPrinter;
        $this->setupPrinter = $setupPrinter;
    }

    /**
     * {@inheritdoc}
     */
    public function listenEvent(Formatter $formatter, Event $event, $eventName)
    {
        $this->printFeatureOnBeforeEvent($formatter, $event);
        $this->captureStepEvent($event);
        $this->printScenarioEvent($formatter, $event);
        $this->printFeatureOnAfterEvent($formatter, $event);
    }

    /**
     * Prints the header for the feature.
     *
     * @param Formatter $formatter
     * @param Event $event
     */
    private function printFeatureOnBeforeEvent(Formatter $formatter, Event $event)
    {
        if (!$event instanceof BeforeFeatureTested) {
            return;
        }
        $this->featurePrinter->printHeader($formatter, $event->getFeature());
    }

    /**
     * Captures step tested event.
     *
     * @param Event $event
     */
    private function captureStepEvent(Event $event)
    {
        if ($event instanceof AfterStepTested) {
            $this->afterStepTestedEvents[$event->getStep()->getLine()] = $event;
        }
        if ($event instanceof AfterStepSetup) {
            $this->afterStepSetupEvents[$event->getStep()->getLine()] = $event;
        }
    }

    /**
     * Prints the scenario tested event.
     *
     * @param Formatter $formatter
     * @param Event $event
     */
    private function printScenarioEvent(Formatter $formatter, Event $event)
    {
        if (!$event instanceof AfterScenarioTested) {
            return;
        }
        $afterScenarioTested = $event;
        $this->scenarioPrinter->printOpenTag(
            $formatter,
            $afterScenarioTested->getFeature(),
            $afterScenarioTested->getScenario(),
            $afterScenarioTested->getTestResult(),
            $event->getFeature()->getFile()
        );

        foreach ($this->afterStepSetupEvents as $afterStepSetup) {
            $this->setupPrinter->printSetup($formatter, $afterStepSetup->getSetup());
        }
        foreach ($this->afterStepTestedEvents as $afterStepTested) {
            $this->stepPrinter->printStep($formatter, $afterScenarioTested->getScenario(), $afterStepTested->getStep(), $afterStepTested->getTestResult());
            $this->setupPrinter->printTeardown($formatter, $afterStepTested->getTeardown());
        }

        $this->afterStepTestedEvents = array();
        $this->afterStepSetupEvents = array();
    }

    /**
     * Prints the feature on AFTER event.
     *
     * @param Formatter $formatter
     * @param Event     $event
     */
    private function printFeatureOnAfterEvent(Formatter $formatter, Event $event)
    {
        if (!$event instanceof AfterFeatureTested) {
            return;
        }
        $this->featurePrinter->printFooter($formatter, $event->getTestResult());
    }
}
