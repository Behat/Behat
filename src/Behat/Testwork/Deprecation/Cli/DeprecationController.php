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
use Behat\Testwork\Deprecation\Result\Interpretation\DeprecationInterpretation;
use Behat\Testwork\EventDispatcher\Event\ExerciseCompleted;
use Behat\Testwork\Tester\Result\ResultInterpreter;
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
        private readonly ResultInterpreter $resultInterpreter,
        private bool $printDeprecations,
        private bool $failOnDeprecations = false,
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
            '--print-behat-deprecations',
            null,
            InputOption::VALUE_NONE,
            'Print Behat deprecation warnings at the end of the test run.'
        );
        $command->addOption(
            '--fail-on-behat-deprecations',
            null,
            InputOption::VALUE_NONE,
            'Exit with error code if Behat deprecations were triggered.'
        );
    }

    public function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $this->output = $output;

        if ($input->getOption('print-behat-deprecations')) {
            $this->printDeprecations = true;
        }

        if ($input->getOption('fail-on-behat-deprecations')) {
            $this->failOnDeprecations = true;
            $this->printDeprecations = true;
        }

        if ($this->failOnDeprecations) {
            $this->resultInterpreter->registerResultInterpretation(
                new DeprecationInterpretation($this->collector)
            );
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
