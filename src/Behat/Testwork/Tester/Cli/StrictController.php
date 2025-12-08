<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester\Cli;

use Behat\Testwork\Cli\Controller;
use Behat\Testwork\Tester\Result\Interpretation\StrictInterpretation;
use Behat\Testwork\Tester\Result\ResultInterpreter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Configures Testwork to interpret test results strictly.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class StrictController implements Controller
{
    /**
     * Initializes controller.
     *
     * @param bool           $strict
     */
    public function __construct(
        private readonly ResultInterpreter $resultInterpreter,
        private $strict = false,
    ) {
    }

    public function configure(Command $command): void
    {
        $command->addOption(
            '--strict',
            null,
            InputOption::VALUE_NONE,
            'Passes only if all tests are explicitly passing.'
        );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->strict && !$input->getOption('strict')) {
            return null;
        }

        $this->resultInterpreter->registerResultInterpretation(new StrictInterpretation());

        return null;
    }
}
