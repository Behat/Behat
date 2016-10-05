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
use Behat\Testwork\Suite\Exception\SuiteNotFoundException;
use Behat\Testwork\Suite\SuiteRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Sets up registered test suites.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class SuiteController implements Controller
{
    /**
     * @var SuiteRegistry
     */
    private $registry;
    /**
     * @var array
     */
    private $suiteConfigurations = array();

    /**
     * Initializes controller.
     *
     * @param SuiteRegistry $registry
     * @param array         $suiteConfigurations
     */
    public function __construct(SuiteRegistry $registry, array $suiteConfigurations)
    {
        $this->registry = $registry;
        $this->suiteConfigurations = $suiteConfigurations;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(Command $command)
    {
        $command->addOption('--suite', '-s', InputOption::VALUE_REQUIRED,
            'Only execute a specific suite.'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $exerciseSuiteName = $input->getOption('suite');

        if (null === $exerciseSuiteName) {
            return;
        }

        if (!isset($this->suiteConfigurations[$exerciseSuiteName])) {
            throw new SuiteNotFoundException(sprintf(
                '`%s` suite is not found or has not been properly registered.',
                $exerciseSuiteName
            ), $exerciseSuiteName);
        }

        $config = $this->suiteConfigurations[$exerciseSuiteName];

        $this->registry->registerSuiteConfiguration(
            $exerciseSuiteName, $config['type'], $config['settings']
        );
    }
}
