<?php

namespace Behat\Behat\Console\Processor;

use Symfony\Component\Console\Command\Command;

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
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class Processor implements ProcessorInterface
{
    /**
     * @see ProcessorInterface::configure()
     */
    public function configure(Command $command)
    {
    }
}
