<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Suite\Cli;

use Behat\Testwork\Cli\Controller;
use Behat\Testwork\Suite\SuiteBootstrapper;
use Behat\Testwork\Suite\SuiteRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Initializes registered test suites.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class InitializationController implements Controller
{
    /**
     * Initializes controller.
     */
    public function __construct(
        private readonly SuiteRepository $repository,
        private readonly SuiteBootstrapper $bootstrapper,
    ) {
    }

    public function configure(Command $command): void
    {
        $command->addOption(
            '--init',
            null,
            InputOption::VALUE_NONE,
            'Initialize all registered test suites.'
        );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('init')) {
            return null;
        }

        $suites = $this->repository->getSuites();
        $this->bootstrapper->bootstrapSuites($suites);

        $output->write(PHP_EOL);

        return 0;
    }
}
