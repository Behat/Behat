<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\EventDispatcher\Cli;

use Behat\Behat\EventDispatcher\Event\AfterScenarioTested;
use Behat\Behat\EventDispatcher\Event\ExampleTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Testwork\Cli\Controller;
use Behat\Testwork\EventDispatcher\Event\AfterExerciseAborted;
use Behat\Testwork\EventDispatcher\Event\AfterSuiteAborted;
use Behat\Testwork\EventDispatcher\Event\ExerciseCompleted;
use Behat\Testwork\EventDispatcher\Event\SuiteTested;
use Behat\Testwork\Tester\Result\Interpretation\ResultInterpretation;
use Behat\Testwork\Tester\Result\Interpretation\SoftInterpretation;
use Behat\Testwork\Tester\Result\Interpretation\StrictInterpretation;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Stops tests on first scenario failure.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class StopOnFailureController implements Controller
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var ResultInterpretation
     */
    private $resultInterpretation;

    /**
     * Initializes controller.
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->resultInterpretation = new SoftInterpretation();
    }

    /**
     * Configures command to be executable by the controller.
     */
    public function configure(Command $command)
    {
        $command->addOption(
            '--stop-on-failure',
            null,
            InputOption::VALUE_NONE,
            'Stop processing on first failed scenario.'
        );
    }

    /**
     * Executes controller.
     *
     * @return null|int
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('stop-on-failure')) {
            return null;
        }

        if ($input->getOption('strict')) {
            $this->resultInterpretation = new StrictInterpretation();
        }

        $this->eventDispatcher->addListener(ScenarioTested::AFTER, [$this, 'exitOnFailure'], -100);
        $this->eventDispatcher->addListener(ExampleTested::AFTER, [$this, 'exitOnFailure'], -100);
    }

    /**
     * Exits if scenario is a failure and if stopper is enabled.
     */
    public function exitOnFailure(AfterScenarioTested $event)
    {
        if (!$this->resultInterpretation->isFailure($event->getTestResult())) {
            return;
        }

        $this->eventDispatcher->dispatch(new AfterSuiteAborted($event->getEnvironment()), SuiteTested::AFTER);
        $this->eventDispatcher->dispatch(new AfterExerciseAborted(), ExerciseCompleted::AFTER);

        exit(1);
    }
}
