<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\EventDispatcher\Cli;

use Behat\Testwork\Cli\Controller;
use Behat\Testwork\EventDispatcher\Event\AfterExerciseAborted;
use Behat\Testwork\EventDispatcher\Event\ExerciseCompleted;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Aborts exercise on SIGINT signal.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class SigintController implements Controller
{
    /**
     * Initializes controller.
     */
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function configure(Command $command): void
    {
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (function_exists('pcntl_signal')) {
            pcntl_async_signals(true);

            pcntl_signal(SIGINT, $this->abortExercise(...));
        }

        return null;
    }

    /**
     * Dispatches AFTER exercise event and exits program.
     */
    public function abortExercise(int $signal = SIGINT): never
    {
        $this->eventDispatcher->dispatch(new AfterExerciseAborted(), ExerciseCompleted::AFTER);

        exit(128 + $signal);
    }
}
