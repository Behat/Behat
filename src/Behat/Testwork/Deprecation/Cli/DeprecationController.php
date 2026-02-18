<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Deprecation\Cli;

use Behat\Testwork\Cli\Controller;
use Behat\Testwork\Deprecation\DeprecationCollector;
use Behat\Testwork\Deprecation\DeprecationPrinter;
use Behat\Testwork\EventDispatcher\Event\ExerciseCompleted;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * CLI controller that prints deprecations at the end of the test run.
 */
final class DeprecationController implements Controller, EventSubscriberInterface
{
    private ?OutputInterface $output = null;

    public function __construct(
        private readonly DeprecationCollector $collector,
        private readonly DeprecationPrinter $printer,
        private bool $printDeprecations,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ExerciseCompleted::AFTER => ['onAfterExercise', -100],
        ];
    }

    public function configure(Command $command): void
    {
        $command->addOption(
            '--print-deprecations',
            null,
            InputOption::VALUE_NONE,
            'Print deprecation warnings at the end of the test run.'
        );
    }

    public function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $this->output = $output;

        if ($input->getOption('print-deprecations')) {
            $this->printDeprecations = true;
        }

        return null;
    }

    public function onAfterExercise(): void
    {
        $this->collector->unregister();

        if ($this->printDeprecations && $this->output instanceof OutputInterface) {
            $this->printer->printDeprecations($this->output);
        }
    }
}
