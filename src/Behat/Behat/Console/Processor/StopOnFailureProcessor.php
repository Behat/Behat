<?php

namespace Behat\Behat\Console\Processor;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\RunControl\EventSubscriber\StopOnFirstFailure;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Stop-on-failure processor.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class StopOnFailureProcessor implements ProcessorInterface
{
    /**
     * @var StopOnFirstFailure
     */
    private $onFailureStopper;

    /**
     * Initializes processor.
     *
     * @param StopOnFirstFailure $onFailureStopper
     */
    public function __construct(StopOnFirstFailure $onFailureStopper)
    {
        $this->onFailureStopper = $onFailureStopper;
    }

    /**
     * Configures command to be able to process it later.
     *
     * @param Command $command
     */
    public function configure(Command $command)
    {
        $command->addOption('--stop-on-failure', null, InputOption::VALUE_NONE,
            'Stop processing on first failed scenario.'
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
        if ($input->getOption('stop-on-failure')) {
            $this->onFailureStopper->enable();
        }
    }

    /**
     * Returns priority of the processor in which it should be configured and executed.
     *
     * @return integer
     */
    public function getPriority()
    {
        return 30;
    }
}
