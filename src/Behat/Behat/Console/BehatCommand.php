<?php

namespace Behat\Behat\Console;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Console\Processor\ProcessorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Behat console command.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class BehatCommand extends Command
{
    private $processors = array();

    /**
     * Initializes command.
     *
     * @param ProcessorInterface[] $processors
     */
    public function __construct(array $processors)
    {
        usort($processors, function(ProcessorInterface $processor1, ProcessorInterface $processor2) {
            return $processor2->getPriority() - $processor1->getPriority();
        });

        $this->processors = $processors;
        parent::__construct('behat');
    }

    /**
     * Configures the command by running processors configure().
     */
    final protected function configure()
    {
        foreach ($this->processors as $processor) {
            $processor->configure($this);
        }
    }

    /**
     * Executes the current command by executing all processors process().
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return integer Return code of one of the processors or 1 if none of them returned integer
     */
    final protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->processors as $processor) {
            if (is_int($return = $processor->process($input, $output))) {
                return $return;
            }
        }

        return 1;
    }
}
