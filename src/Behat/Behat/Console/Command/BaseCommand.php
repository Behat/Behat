<?php

namespace Behat\Behat\Console\Command;

use Symfony\Component\Console\Command\Command;

use Behat\Behat\Console\Processor\ProcessorInterface;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Base behat console command.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class BaseCommand extends Command
{
    private $processors = array();

    protected function setProcessors(array $processors)
    {
        $this->processors = array();
        foreach ($processors as $processor) {
            $this->addProcessor($processor);
        }
    }

    protected function addProcessor(ProcessorInterface $processor)
    {
        $this->processors[] = $processor;
    }

    protected function getProcessorsInputOptions()
    {
        $options = array();

        foreach ($this->processors as $processor) {
            $options = array_merge($options, $processor->getInputOptions());
        }

        return $options;
    }

    protected function getProcessors()
    {
        return $this->processors;
    }
}
