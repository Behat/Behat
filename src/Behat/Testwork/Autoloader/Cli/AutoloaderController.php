<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Autoloader\Cli;

use Behat\Testwork\Cli\Controller;
use Composer\Autoload\ClassLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Registers Testwork autoloader.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class AutoloaderController implements Controller
{
    /**
     * Initializes controller.
     */
    public function __construct(
        private readonly ClassLoader $loader,
    ) {
    }

    public function configure(Command $command): void
    {
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->loader->register();

        return null;
    }
}
