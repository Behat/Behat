<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\EventDispatcher\Cli;

use Behat\Testwork\Cli\Controller;
use Behat\Testwork\Tester\Handler\StopOnFailureHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Stops tests on first scenario failure.
 *
 * TODO this should be moved in the Behat\Testwork\Tester\Cli namespace
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class StopOnFailureController implements Controller
{
    /**
     * @var StopOnFailureHandler
     */
    private $stopOnFailureHandler;

    /**
     * @required
     */
    public function setStopOnFailureHandler(StopOnFailureHandler $stopOnFailureHandler)
    {
        $this->stopOnFailureHandler = $stopOnFailureHandler;
    }

    /**
     * Configures command to be executable by the controller.
     */
    public function configure(Command $command)
    {
        $command->addOption(
            '--stop-on-failure',
            null,
            InputOption::VALUE_NONE,
            'Stop processing on first failed scenario.'
        );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('stop-on-failure')) {
            return null;
        }

        $this->stopOnFailureHandler->registerListeners();

        return null;
    }
}
