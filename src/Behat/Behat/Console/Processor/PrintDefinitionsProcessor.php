<?php

namespace Behat\Behat\Console\Processor;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Definition\UseCase\PrintDefinitions;
use Behat\Behat\Event\EventInterface;
use Behat\Behat\EventDispatcher\DispatchingService;
use Behat\Behat\Suite\Event\SuitesCarrierEvent;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Definitions printer processor.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class PrintDefinitionsProcessor extends DispatchingService implements ProcessorInterface
{
    /**
     * @var PrintDefinitions
     */
    private $useCase;

    /**
     * Initializes processor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param PrintDefinitions         $useCase
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, PrintDefinitions $useCase)
    {
        parent::__construct($eventDispatcher);

        $this->useCase = $useCase;
    }

    /**
     * Configures command to be able to process it later.
     *
     * @param Command $command
     */
    public function configure(Command $command)
    {
        $command
            ->addOption('--definitions', '-d', InputOption::VALUE_REQUIRED,
                "Print all available step definitions:" . PHP_EOL .
                "- use <info>-dl</info> to just list definition expressions." . PHP_EOL .
                "- use <info>-di</info> to show definitions with extended info." . PHP_EOL .
                "- use <info>-d 'needle'</info> to find specific definitions." . PHP_EOL .
                "Use <info>--lang</info> to see definitions in specific language."
            );
    }

    /**
     * Processes data from container and console input.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return null|integer
     */
    public function process(InputInterface $input, OutputInterface $output)
    {
        if (null === $type = $input->getOption('definitions')) {
            return null;
        }

        $suitesProvider = new SuitesCarrierEvent();
        $this->dispatch(EventInterface::LOAD_SUITES, $suitesProvider);

        if ($input->getOption('suite')) {
            $suites = array($suitesProvider->getSuite($input->getOption('suite')));
        } else {
            $suites = $suitesProvider->getSuites();
        }

        $this->useCase->printDefinitions($output, $suites, $input->getOption('lang') ? : 'en', $type);

        return 0;
    }

    /**
     * Returns priority of the processor in which it should be configured and executed.
     *
     * @return integer
     */
    public function getPriority()
    {
        return 60;
    }
}
