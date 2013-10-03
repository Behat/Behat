<?php

namespace Behat\Behat\Console\Processor;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\RunControl\UseCase\CacheFailedScenariosForRerun;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Rerun initialization processor.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class RerunProcessor implements ProcessorInterface
{
    /**
     * @var CacheFailedScenariosForRerun
     */
    private $useCase;

    /**
     * Initializes processor.
     *
     * @param CacheFailedScenariosForRerun $useCase
     */
    public function __construct(CacheFailedScenariosForRerun $useCase)
    {
        $this->useCase = $useCase;
    }

    /**
     * Configures command to be able to process it later.
     *
     * @param Command $command
     */
    public function configure(Command $command)
    {
        $command->addOption('--rerun', null, InputOption::VALUE_NONE,
            'Re-run scenarios that failed during last execution.'
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
        $this->useCase->setKey(md5(
            $input->getParameterOption(array('--profile', '-p')) .
            $input->getOption('suite') .
            implode(' ', $input->getOption('name')) .
            implode(' ', $input->getOption('tags')) .
            $input->getOption('role') .
            $input->getArgument('features')
        ));

        if (!$input->getOption('rerun')) {
            return;
        }
        if (!$this->useCase->getFileName() || !file_exists($this->useCase->getFileName())) {
            return;
        }

        $input->setArgument('features', $this->useCase->getFileName());
    }

    /**
     * Returns priority of the processor in which it should be configured and executed.
     *
     * @return integer
     */
    public function getPriority()
    {
        return 10;
    }
}
