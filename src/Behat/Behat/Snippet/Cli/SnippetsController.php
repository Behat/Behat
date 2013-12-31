<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Snippet\Cli;

use Behat\Behat\Snippet\AggregateSnippet;
use Behat\Behat\Snippet\Printer\SnippetPrinter;
use Behat\Behat\Snippet\SnippetRegistry;
use Behat\Behat\Snippet\SnippetWriter;
use Behat\Behat\Tester\Event\StepTested;
use Behat\Behat\Tester\Result\TestResult;
use Behat\Testwork\Cli\Controller;
use Behat\Testwork\Printer\OutputPrinter;
use Behat\Testwork\Tester\Event\ExerciseTested;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Snippets controller.
 *
 * Appends and prints snippets.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class SnippetsController implements Controller, SnippetPrinter
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
     * @var OutputPrinter
     */
    private $printer;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * Initializes controller.
     *
     * @param SnippetRegistry          $registry
     * @param SnippetWriter            $writer
     * @param OutputPrinter            $printer
     * @param EventDispatcherInterface $eventDispatcher
     * @param TranslatorInterface      $translator
     */
    public function __construct(
        SnippetRegistry $registry,
        SnippetWriter $writer,
        OutputPrinter $printer,
        EventDispatcherInterface $eventDispatcher,
        TranslatorInterface $translator
    ) {
        $this->registry = $registry;
        $this->writer = $writer;
        $this->printer = $printer;
        $this->eventDispatcher = $eventDispatcher;
        $this->translator = $translator;
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

        if ($input->getOption('append-snippets')) {
            $this->eventDispatcher->addListener(ExerciseTested::AFTER, array($this, 'appendAllSnippets'), -999);
        }

        if (!$input->getOption('no-snippets') && !$input->getOption('append-snippets')) {
            $this->eventDispatcher->addListener(ExerciseTested::AFTER, array($this, 'printAllSnippets'), -999);
        }
    }

    /**
     * Registers undefined step.
     *
     * @param StepTested $event
     */
    public function registerUndefinedStep(StepTested $event)
    {
        if (TestResult::UNDEFINED === $event->getResultCode()) {
            $this->registry->registerUndefinedStep($event->getEnvironment(), $event->getStep());
        }
    }

    /**
     * Appends all snippets to corresponding targets.
     */
    public function appendAllSnippets()
    {
        $snippets = $this->registry->getSnippets();
        count($snippets) && $this->printer->writeln();
        $this->writer->appendSnippets($snippets);
    }

    /**
     * Prints all snippets to the console.
     */
    public function printAllSnippets()
    {
        $snippets = $this->registry->getSnippets();
        count($snippets) && $this->printer->writeln();
        $this->writer->printSnippets($this, $snippets);
    }

    /**
     * Prints snippets of specific target.
     *
     * @param string             $target
     * @param AggregateSnippet[] $snippets
     */
    public function printSnippets($target, array $snippets)
    {
        $message = $this->translator->trans('snippet_proposal_title', array('%1%' => $target), 'output');

        $this->printer->writeln('--- ' . $message . PHP_EOL);

        foreach ($snippets as $snippet) {
            $this->printer->writeln(sprintf('{+undefined}%s{-undefined}', $snippet->getSnippet()) . PHP_EOL);
        }
    }
}
