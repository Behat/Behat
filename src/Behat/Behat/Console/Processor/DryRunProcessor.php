<?php

namespace Behat\Behat\Console\Processor;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Hook\EventSubscriber\HookDispatcher;
use Behat\Behat\Tester\EventSubscriber\TesterDispatcher;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Dry-run processor.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class DryRunProcessor implements ProcessorInterface
{
    /**
     * @var TesterDispatcher
     */
    private $testerDispatcher;
    /**
     * @var HookDispatcher
     */
    private $hookDispatcher;

    /**
     * Initializes processor.
     *
     * @param TesterDispatcher $testerDispatcher
     * @param HookDispatcher   $hookDispatcher
     */
    public function __construct(TesterDispatcher $testerDispatcher, HookDispatcher $hookDispatcher)
    {
        $this->testerDispatcher = $testerDispatcher;
        $this->hookDispatcher = $hookDispatcher;
    }

    /**
     * Configures command to be able to process it later.
     *
     * @param Command $command
     */
    public function configure(Command $command)
    {
        $command->addOption('--dry-run', null, InputOption::VALUE_NONE,
            'Invokes formatters without executing the steps & hooks.'
        );
    }

    /**
     * Processes data from console input.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function process(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('dry-run')) {
            return;
        }

        $this->testerDispatcher->skipStepTests();
        $this->hookDispatcher->skipHooks();
    }

    /**
     * Returns priority of the processor in which it should be configured and executed.
     *
     * @return integer
     */
    public function getPriority()
    {
        return 20;
    }
}
