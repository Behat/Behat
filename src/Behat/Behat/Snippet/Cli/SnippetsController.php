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
use Behat\Behat\Tester\Result\StepResult;
use Behat\Testwork\Cli\Controller;
use Behat\Testwork\EventDispatcher\Event\ExerciseCompleted;
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
     * @var SnippetRegistry
     */
    private $registry;
    /**
     * @var SnippetWriter
     */
    private $writer;
    /**
     * @var ConsoleSnippetPrinter
     */
    private $printer;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * Initializes controller.
     *
     * @param SnippetRegistry          $registry
     * @param SnippetWriter            $writer
     * @param ConsoleSnippetPrinter    $printer
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        SnippetRegistry $registry,
        SnippetWriter $writer,
        ConsoleSnippetPrinter $printer,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->registry = $registry;
        $this->writer = $writer;
        $this->printer = $printer;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Configures command to be executable by the controller.
     *
     * @param Command $command
     */
    public function configure(Command $command)
    {
        $command
            ->addOption(
                '--append-snippets', null, InputOption::VALUE_NONE,
                "Appends snippets for undefined steps into main context."
            )
            ->addOption(
                '--no-snippets', null, InputOption::VALUE_NONE,
                "Do not print snippets for undefined steps after stats."
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
        $this->eventDispatcher->addListener(StepTested::AFTER, array($this, 'registerUndefinedStep'), -999);
        $this->output = $output;

        if ($input->getOption('append-snippets')) {
            $this->eventDispatcher->addListener(ExerciseCompleted::AFTER, array($this, 'appendAllSnippets'), -999);
        }

        if (!$input->getOption('no-snippets') && !$input->getOption('append-snippets')) {
            $this->eventDispatcher->addListener(ExerciseCompleted::AFTER, array($this, 'printAllSnippets'), -999);
        }

        if (!$input->getOption('no-snippets')) {
            $this->eventDispatcher->addListener(ExerciseCompleted::AFTER, array($this, 'printUndefinedSteps'), -995);
        }
    }

    /**
     * Registers undefined step.
     *
     * @param AfterStepTested $event
     */
    public function registerUndefinedStep(AfterStepTested $event)
    {
        if (StepResult::UNDEFINED === $event->getTestResult()->getResultCode()) {
            $this->registry->registerUndefinedStep($event->getEnvironment(), $event->getStep());
        }
    }

    /**
     * Appends all snippets to corresponding targets.
     */
    public function appendAllSnippets()
    {
        $snippets = $this->registry->getSnippets();
        count($snippets) && $this->output->writeln('');

        $this->writer->appendSnippets($snippets);
    }

    /**
     * Prints all snippets.
     */
    public function printAllSnippets()
    {
        $snippets = $this->registry->getSnippets();
        count($snippets) && $this->output->writeln('');

        $this->writer->printSnippets($this->printer, $snippets);
    }

    /**
     * Prints all undefined steps.
     */
    public function printUndefinedSteps()
    {
        $undefined = $this->registry->getUndefinedSteps();
        count($undefined) && $this->output->writeln('');

        $this->writer->printUndefinedSteps($this->printer, $undefined);
    }
}
