<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\EventListener\AST;

use Behat\Behat\EventDispatcher\Event\AfterStepSetup;
use Behat\Behat\EventDispatcher\Event\AfterStepTested;
use Behat\Behat\EventDispatcher\Event\ExampleTested;
use Behat\Behat\EventDispatcher\Event\ScenarioLikeTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Behat\Output\Node\Printer\SetupPrinter;
use Behat\Behat\Output\Node\Printer\StepPrinter;
use Behat\Gherkin\Node\ScenarioLikeInterface;
use Behat\Testwork\Event\Event;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\EventListener\EventListener;

/**
 * Listens to step events and call appropriate printers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class StepListener implements EventListener
{
    /**
     * @var StepPrinter
     */
    private $stepPrinter;
    /**
     * @var ScenarioLikeInterface
     */
    private $scenario;
    /**
     * @var null|SetupPrinter
     */
    private $setupPrinter;

    /**
     * Initializes listener.
     *
     * @param StepPrinter       $stepPrinter
     * @param null|SetupPrinter $setupPrinter
     */
    public function __construct(StepPrinter $stepPrinter, SetupPrinter $setupPrinter = null)
    {
        $this->stepPrinter = $stepPrinter;
        $this->setupPrinter = $setupPrinter;
    }

    /**
     * {@inheritdoc}
     */
    public function listenEvent(Formatter $formatter, Event $event, $eventName)
    {
        $this->captureScenarioOnScenarioEvent($event);
        $this->forgetScenarioOnAfterEvent($eventName);
        $this->printStepSetupOnBeforeEvent($formatter, $event);
        $this->printStepOnAfterEvent($formatter, $event);
    }

    /**
     * Captures scenario into the ivar on scenario/background/example BEFORE event.
     *
     * @param Event $event
     */
    private function captureScenarioOnScenarioEvent(Event $event)
    {
        if (!$event instanceof ScenarioLikeTested) {
            return;
        }

        $this->scenario = $event->getScenario();
    }

    /**
     * Removes scenario from the ivar on scenario/background/example AFTER event.
     *
     * @param string $eventName
     */
    private function forgetScenarioOnAfterEvent($eventName)
    {
        if (!in_array($eventName, array(ScenarioTested::AFTER, ExampleTested::AFTER))) {
            return;
        }

        $this->scenario = null;
    }

    private function printStepSetupOnBeforeEvent(Formatter $formatter, Event $event)
    {
        if (!$event instanceof AfterStepSetup) {
            return;
        }

        if ($this->setupPrinter) {
            $this->setupPrinter->printSetup($formatter, $event->getSetup());
        }
    }

    /**
     * Prints step on AFTER event.
     *
     * @param Formatter $formatter
     * @param Event     $event
     */
    private function printStepOnAfterEvent(Formatter $formatter, Event $event)
    {
        if (!$event instanceof AfterStepTested) {
            return;
        }

        $this->stepPrinter->printStep($formatter, $this->scenario, $event->getStep(), $event->getTestResult());

        if ($this->setupPrinter) {
            $this->setupPrinter->printTeardown($formatter, $event->getTeardown());
        }
    }
}
