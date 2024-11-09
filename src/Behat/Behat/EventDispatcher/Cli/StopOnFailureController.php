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
use Behat\Testwork\EventDispatcher\TestworkEventDispatcher;
use Behat\Testwork\Tester\Handler\StopOnFailureHandler;
use Behat\Testwork\Tester\Result\Interpretation\ResultInterpretation;
use Behat\Testwork\Tester\Result\Interpretation\SoftInterpretation;
use Behat\Testwork\Tester\Result\Interpretation\StrictInterpretation;
use Behat\Testwork\Tester\Result\ResultInterpreter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Stops tests on first scenario failure.
 * 
 * TODO this should be moved in the Behat\Testwork\Tester\Cli namespace
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class StopOnFailureController implements Controller
{
     /**
      * @deprecated events are now dispatched in the StopOnFailureHandler
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var StopOnFailureHandler
     */
    private $stopOnFailureHandler;

    /**
     * Initializes controller.
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @required
     */
    public function setStopOnFailureHandler(StopOnFailureHandler $stopOnFailureHandler)
    {
        $this->stopOnFailureHandler = $stopOnFailureHandler;
    }

    /**
     * Configures command to be executable by the controller.
     *
     * @param Command $command
     */
    public function configure(Command $command)
    {
        $command->addOption('--stop-on-failure', null, InputOption::VALUE_NONE,
            'Stop processing on first failed scenario.'
        );
    }

    /**
     * Executes controller.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return null|integer
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('stop-on-failure')) {
            return null;
        }

        $this->stopOnFailureHandler->registerListeners();
    }

}
