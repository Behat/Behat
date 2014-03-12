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
use Behat\Testwork\Output\Printer\OutputPrinter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Testwork exception verbosity controller.
 *
 * Controls exception verbosity level.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class VerbosityController implements Controller
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
        if ($input->hasParameterOption('-vvv') || $input->hasParameterOption('--verbose=3') || $input->getParameterOption('--verbose') === 3) {
            $this->exceptionPresenter->setDefaultVerbosity(OutputPrinter::VERBOSITY_DEBUG);
        } elseif ($input->hasParameterOption('-vv') || $input->hasParameterOption('--verbose=2') || $input->getParameterOption('--verbose') === 2) {
            $this->exceptionPresenter->setDefaultVerbosity(OutputPrinter::VERBOSITY_VERY_VERBOSE);
        } elseif ($input->hasParameterOption('-v') || $input->hasParameterOption('--verbose=1') || $input->hasParameterOption('--verbose') || $input->getParameterOption('--verbose')) {
            $this->exceptionPresenter->setDefaultVerbosity(OutputPrinter::VERBOSITY_VERBOSE);
        }
    }
}
