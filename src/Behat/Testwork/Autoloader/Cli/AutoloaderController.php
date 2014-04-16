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
use Symfony\Component\ClassLoader\ClassLoader;
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
     * @var ClassLoader
     */
    private $loader;

    /**
     * Initializes controller
     *
     * @param ClassLoader $loader
     */
    public function __construct(ClassLoader $loader)
    {
        $this->loader = $loader;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(Command $command)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->loader->register();
    }
}
