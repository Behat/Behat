<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\EventListener\JSON;

use Behat\Behat\EventDispatcher\Event\AfterFeatureSetup;
use Behat\Behat\EventDispatcher\Event\AfterFeatureTested;
use Behat\Behat\EventDispatcher\Event\AfterScenarioSetup;
use Behat\Behat\EventDispatcher\Event\AfterScenarioTested;
use Behat\Behat\EventDispatcher\Event\AfterStepSetup;
use Behat\Behat\EventDispatcher\Event\AfterStepTested;
use Behat\Behat\Output\Node\Printer\ExercisePrinter;
use Behat\Behat\Output\Node\Printer\FeaturePrinter;
use Behat\Behat\Output\Node\Printer\ScenarioPrinter;
use Behat\Behat\Output\Node\Printer\SetupPrinter;
use Behat\Behat\Output\Node\Printer\StepPrinter;
use Behat\Behat\Output\Node\Printer\SuitePrinter;
use Behat\Gherkin\Node\ScenarioLikeInterface;
use Behat\Testwork\Event\Event;
use Behat\Testwork\EventDispatcher\Event\AfterExerciseCompleted;
use Behat\Testwork\EventDispatcher\Event\AfterExerciseSetup;
use Behat\Testwork\EventDispatcher\Event\AfterSuiteSetup;
use Behat\Testwork\EventDispatcher\Event\AfterSuiteTested;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\EventListener\EventListener;

final class JSONElementListener implements EventListener
{
    private ScenarioLikeInterface $currentScenario;

    public function __construct(
        private readonly ExercisePrinter $exercisePrinter,
        private readonly SuitePrinter $suitePrinter,
        private readonly FeaturePrinter $featurePrinter,
        private readonly ScenarioPrinter $scenarioPrinter,
        private readonly StepPrinter $stepPrinter,
        private readonly SetupPrinter $setupPrinter,
    ) {
    }

    public function listenEvent(Formatter $formatter, Event $event, $eventName): void
    {
        $this->onExerciseStart($formatter, $event);
        $this->onSuiteStart($formatter, $event);
        $this->onFeatureStart($formatter, $event);
        $this->onScenarioStart($formatter, $event);
        $this->onStepStart($formatter, $event);
        $this->onStepEnd($formatter, $event);
        $this->onScenarioEnd($formatter, $event);
        $this->onFeatureEnd($formatter, $event);
        $this->onSuiteEnd($formatter, $event);
        $this->onExerciseEnd($formatter, $event);
    }

    private function onExerciseStart(Formatter $formatter, Event $event): void
    {
        if ($event instanceof AfterExerciseSetup) {
            $this->exercisePrinter->printHeader($formatter);
        }
    }

    private function onExerciseEnd(Formatter $formatter, Event $event): void
    {
        if ($event instanceof AfterExerciseCompleted) {
            $this->exercisePrinter->printFooter($formatter);
        }
    }

    private function onSuiteStart(Formatter $formatter, Event $event): void
    {
        if ($event instanceof AfterSuiteSetup) {
            $this->suitePrinter->printHeader($formatter, $event->getSuite());
            $this->setupPrinter->printSetup($formatter, $event->getSetup());
        }
    }

    private function onSuiteEnd(Formatter $formatter, Event $event): void
    {
        if ($event instanceof AfterSuiteTested) {
            $this->setupPrinter->printTeardown($formatter, $event->getTeardown());
            $this->suitePrinter->printFooter($formatter, $event->getSuite());
        }
    }

    private function onFeatureStart(Formatter $formatter, Event $event): void
    {
        if ($event instanceof AfterFeatureSetup) {
            $this->featurePrinter->printHeader($formatter, $event->getFeature());
            $this->setupPrinter->printSetup($formatter, $event->getSetup());
        }
    }

    private function onFeatureEnd(Formatter $formatter, Event $event): void
    {
        if ($event instanceof AfterFeatureTested) {
            $this->setupPrinter->printTeardown($formatter, $event->getTeardown());
            $this->featurePrinter->printFooter($formatter, $event->getTestResult());
        }
    }

    private function onScenarioStart(Formatter $formatter, Event $event): void
    {
        if ($event instanceof AfterScenarioSetup) {
            $this->scenarioPrinter->printHeader($formatter, $event->getFeature(), $event->getScenario());
            $this->setupPrinter->printSetup($formatter, $event->getSetup());
            $this->currentScenario = $event->getScenario();
        }
    }

    private function onScenarioEnd(Formatter $formatter, Event $event): void
    {
        if ($event instanceof AfterScenarioTested) {
            $this->scenarioPrinter->printFooter($formatter, $event->getTestResult());
            $this->setupPrinter->printTeardown($formatter, $event->getTeardown());
        }
    }

    private function onStepStart(Formatter $formatter, $event): void
    {
        if ($event instanceof AfterStepSetup) {
            $this->setupPrinter->printSetup($formatter, $event->getSetup());
        }
    }

    private function onStepEnd(Formatter $formatter, Event $event): void
    {
        if ($event instanceof AfterStepTested) {
            $this->stepPrinter->printStep($formatter, $this->currentScenario, $event->getStep(), $event->getTestResult());
            $this->setupPrinter->printTeardown($formatter, $event->getTeardown());
        }
    }
}
