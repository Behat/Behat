<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Snippet\Cli;

use Behat\Behat\EventDispatcher\Event\AfterStepTested;
use Behat\Behat\EventDispatcher\Event\StepTested;
use Behat\Behat\Snippet\Printer\ConsoleSnippetPrinter;
use Behat\Behat\Snippet\SnippetRegistry;
use Behat\Behat\Snippet\SnippetWriter;
use Behat\Testwork\Cli\Controller;
use Behat\Testwork\EventDispatcher\Event\ExerciseCompleted;
use Behat\Testwork\Tester\Result\TestResult;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Appends and prints snippets.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class SnippetsController implements Controller
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * Initializes controller.
     */
    public function __construct(
        private readonly SnippetRegistry $registry,
        private readonly SnippetWriter $writer,
        private readonly ConsoleSnippetPrinter $printer,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * Configures command to be executable by the controller.
     */
    public function configure(Command $command): void
    {
        $command
            ->addOption(
                '--append-snippets',
                null,
                InputOption::VALUE_NONE,
                'Appends snippets for undefined steps into main context.'
            )
            ->addOption(
                '--no-snippets',
                null,
                InputOption::VALUE_NONE,
                'Do not print snippets for undefined steps after stats.'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->eventDispatcher->addListener(StepTested::AFTER, $this->registerUndefinedStep(...), -999);
        $this->output = $output;

        if ($input->getOption('append-snippets')) {
            $this->eventDispatcher->addListener(ExerciseCompleted::AFTER, $this->appendAllSnippets(...), -999);
        }

        if (!$input->getOption('no-snippets') && !$input->getOption('append-snippets')) {
            $this->eventDispatcher->addListener(ExerciseCompleted::AFTER, $this->printAllSnippets(...), -999);
        }

        if (!$input->getOption('no-snippets')) {
            $this->eventDispatcher->addListener(ExerciseCompleted::AFTER, $this->printSnippetGenerationFailures(...), -999);
        }

        if (!$input->getOption('no-snippets')) {
            $this->eventDispatcher->addListener(ExerciseCompleted::AFTER, $this->printUndefinedSteps(...), -995);
        }

        return null;
    }

    /**
     * Registers undefined step.
     */
    public function registerUndefinedStep(AfterStepTested $event): void
    {
        if (TestResult::UNDEFINED === $event->getTestResult()->getResultCode()) {
            $this->registry->registerUndefinedStep($event->getEnvironment(), $event->getStep());
        }
    }

    /**
     * Appends all snippets to corresponding targets.
     */
    public function appendAllSnippets(): void
    {
        $snippets = $this->registry->getSnippets();
        if ($snippets) {
            $this->output->writeln('');
        }

        $this->writer->appendSnippets($snippets);
    }

    /**
     * Prints all snippets.
     */
    public function printAllSnippets(): void
    {
        $snippets = $this->registry->getSnippets();
        if ($snippets) {
            $this->output->writeln('');
        }

        $this->writer->printSnippets($this->printer, $snippets);
    }

    private function printSnippetGenerationFailures(): void
    {
        $failures = $this->registry->getGenerationFailures();
        if ($failures !== []) {
            $this->output->writeln('');
        }

        $this->printer->printSnippetGenerationFailures($failures);
    }

    /**
     * Prints all undefined steps.
     */
    public function printUndefinedSteps(): void
    {
        $undefined = $this->registry->getUndefinedSteps();
        if ($undefined) {
            $this->output->writeln('');
        }

        $this->writer->printUndefinedSteps($this->printer, $undefined);
    }
}
