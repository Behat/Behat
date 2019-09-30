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
     * @var ResultInterpreter
     */
    private $resultInterpreter;
    /**
     * @var bool
     */
    private $strict;

    /**
     * Initializes controller.
     *
     * @param ResultInterpreter $resultInterpreter
     * @param bool           $strict
     */
    public function __construct(ResultInterpreter $resultInterpreter, $strict = false)
    {
        $this->resultInterpreter = $resultInterpreter;
        $this->strict = $strict;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(Command $command)
    {
        $command->addOption('--strict', null, InputOption::VALUE_NONE,
            'Passes only if all tests are explicitly passing.'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->strict && !$input->getOption('strict')) {
            return;
        }

        $this->resultInterpreter->registerResultInterpretation(new StrictInterpretation());
    }
}
