<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition\Cli;

use Behat\Behat\Definition\Call\RuntimeDefinition;
use Behat\Behat\Definition\DefinitionRepository;
use Behat\Behat\Definition\Printer\ConsoleDefinitionInformationPrinter;
use Behat\Testwork\Cli\Controller;
use Behat\Testwork\EventDispatcher\Event\AfterSuiteTested;
use Behat\Testwork\EventDispatcher\Event\ExerciseCompleted;
use Behat\Testwork\EventDispatcher\Event\SuiteTested;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Prints unused definitions
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class UnusedDefinitionsController implements Controller
{
    /**
     * @var array <string, array{definition: RuntimeDefinition, used: bool}>
     */
    private array $definitionUsage = [];

    public function __construct(
        private DefinitionRepository $definitionRepository,
        private EventDispatcherInterface $eventDispatcher,
        private ConsoleDefinitionInformationPrinter $printer,
    ) {
    }

    public function configure(Command $command): void
    {
        $command
            ->addOption(
                '--print-unused-definitions', null, InputOption::VALUE_NONE,
                "Reports definitions that were never used."
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): ?int
    {
        if ($input->getOption('print-unused-definitions')) {
            $this->eventDispatcher->addListener(SuiteTested::AFTER, array($this, 'registerDefinitionUsages'), -999);
            $this->eventDispatcher->addListener(ExerciseCompleted::AFTER, array($this, 'printUnusedDefinitions'), -999);
        }
        return null;
    }

    public function registerDefinitionUsages(AfterSuiteTested $event): void
    {
        $environmentDefinitions = $this->definitionRepository->getEnvironmentDefinitions($event->getEnvironment());
        foreach ($environmentDefinitions as $definition) {
            if ($definition instanceof RuntimeDefinition) {
                $path = $definition->getPath();
                if (!isset($this->definitionUsage[$path])) {
                    $this->definitionUsage[$path] = [
                        'definition' => $definition,
                        'used' => $definition->hasBeenUsed(),
                    ];
                } elseif ($definition->hasBeenUsed()) {
                    $this->definitionUsage[$path]['used'] = true;
                }
            }
        }
    }

    public function printUnusedDefinitions(): void
    {
        $unusedDefinitions = array_filter($this->definitionUsage, function ($definition) {
            return $definition['used'] === false;
        });
        $unusedDefinitions = array_column($unusedDefinitions, 'definition');

        $this->printer->printUnusedDefinitions($unusedDefinitions);
    }
}
