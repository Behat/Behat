<?php

namespace Behat\Behat\Console\Processor;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\RunControl\EventSubscriber\CacheFailedScenariosForRerun;
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
    private $cache;

    /**
     * Initializes processor.
     *
     * @param CacheFailedScenariosForRerun $cache
     */
    public function __construct(CacheFailedScenariosForRerun $cache)
    {
        $this->cache = $cache;
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
        $this->cache->setKey(md5(
            $input->getParameterOption(array('--profile', '-p')) .
            $input->getOption('suite') .
            $input->getOption('tags') .
            $input->getOption('role') .
            $input->getOption('name') .
            $input->getArgument('features')
        ));

        if (!$input->getOption('rerun')) {
            return;
        }
        if (!$this->cache->getFileName() || !file_exists($this->cache->getFileName())) {
            return;
        }

        $input->setArgument('features', $this->cache->getFileName());
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
