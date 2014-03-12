<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Exception\Cli;

use Behat\Testwork\Cli\Controller;
use Behat\Testwork\Exception\ExceptionPresenter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Controls exception default verbosity level.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class VerbosityController implements Controller
{
    /**
     * @var ExceptionPresenter
     */
    private $exceptionPresenter;

    /**
     * Initializes controller.
     *
     * @param ExceptionPresenter $exceptionPresenter
     */
    public function __construct(ExceptionPresenter $exceptionPresenter)
    {
        $this->exceptionPresenter = $exceptionPresenter;
    }

    /**
     * Configures command to be executable by the controller.
     *
     * @param Command $command
     */
    public function configure(Command $command)
    {
    }

    /**
     * Executes controller.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return null|integer
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if ($output->getVerbosity() !== OutputInterface::VERBOSITY_NORMAL) {
            $this->exceptionPresenter->setDefaultVerbosity($output->getVerbosity());
        }
    }
}
