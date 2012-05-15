<?php

namespace Behat\Behat\Console\Processor;

use Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input\InputInterface;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Abstract base processor.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class Processor implements ProcessorInterface
{
    /**
     * Configures command to be able to process it later.
     *
     * @param Command $command
     */
    public function configure(Command $command)
    {
    }

    /**
     * Returns correct value for input switch.
     *
     * @param InputInterface $input
     * @param string         $name
     *
     * @return Boolean|null
     */
    protected function getSwitchValue(InputInterface $input, $name)
    {
        if ($input->getOption($name)) {
            return true;
        }
        if ($input->getOption('no-'.$name)) {
            return false;
        }

        return null;
    }
}
