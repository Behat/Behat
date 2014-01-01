<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition\Cli;

use Behat\Behat\Definition\DefinitionWriter;
use Behat\Behat\Definition\Printer\ConsoleDefinitionInformationPrinter;
use Behat\Behat\Definition\Printer\ConsoleDefinitionListPrinter;
use Behat\Behat\Definition\Printer\DefinitionPrinter;
use Behat\Testwork\Cli\Controller;
use Behat\Testwork\Suite\SuiteRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Available definitions controller.
 *
 * Shows all currently available definitions to the user.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class AvailableDefinitionsController implements Controller
{
    /**
     * @var SuiteRepository
     */
    private $suiteRepository;
    /**
     * @var DefinitionWriter
     */
    private $writer;
    /**
     * @var ConsoleDefinitionListPrinter
     */
    private $listPrinter;
    /**
     * @var ConsoleDefinitionInformationPrinter
     */
    private $infoPrinter;

    /**
     * Initializes controller.
     *
     * @param SuiteRepository                     $suiteRepository
     * @param DefinitionWriter                    $writer
     * @param ConsoleDefinitionListPrinter        $listPrinter
     * @param ConsoleDefinitionInformationPrinter $infoPrinter
     */
    public function __construct(
        SuiteRepository $suiteRepository,
        DefinitionWriter $writer,
        ConsoleDefinitionListPrinter $listPrinter,
        ConsoleDefinitionInformationPrinter $infoPrinter
    ) {
        $this->suiteRepository = $suiteRepository;
        $this->writer = $writer;
        $this->listPrinter = $listPrinter;
        $this->infoPrinter = $infoPrinter;
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
                '--definitions',
                '-d',
                InputOption::VALUE_REQUIRED,
                "Print all available step definitions:" . PHP_EOL .
                "- use <info>-dl</info> to just list definition expressions." . PHP_EOL .
                "- use <info>-di</info> to show definitions with extended info." . PHP_EOL .
                "- use <info>-d 'needle'</info> to find specific definitions." . PHP_EOL .
                "Use <info>--lang</info> to see definitions in specific language."
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
        if (null === $argument = $input->getOption('definitions')) {
            return null;
        }

        $printer = $this->getDefinitionPrinter($argument);
        foreach ($this->suiteRepository->getSuites() as $suite) {
            $this->writer->printSuiteDefinitions($printer, $suite);
        }

        return 0;
    }

    /**
     * Returns definition printer for provided option argument.
     *
     * @param string $argument
     *
     * @return DefinitionPrinter
     */
    protected function getDefinitionPrinter($argument)
    {
        if ('i' === $argument) {
            return $this->infoPrinter;
        }

        if ('l' !== $argument) {
            $this->infoPrinter->setSearchCriterion($argument);

            return $this->infoPrinter;
        }

        return $this->listPrinter;
    }
}
