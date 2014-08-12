<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester\Cli;

use Behat\Behat\Tester\Exception\BadPriorityException;
use Behat\Behat\Tester\Priority\Exercise;
use Behat\Behat\Tester\Priority\Prioritiser\ReversePrioritiser;
use Behat\Behat\Tester\Priority\Prioritiser;
use Behat\Testwork\Cli\Controller;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Process\Exception\InvalidArgumentException;

/**
 * Preloads scenarios and then modifies the order when --priority is passed
 *
 * @author Ciaran McNulty <mail@ciaranmcnulty.com>
 */
final class PriorityController implements Controller
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    /**
     * @var Exercise
     */
    private $exercise;
    /**
     * @var array
     */
    private $prioritisers = array();

    /**
     * Initializes controller.
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, Exercise $exercise)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->exercise = $exercise;
    }

    /**
     * Configures command to be executable by the controller.
     *
     * @param SymfonyCommand $command
     */
    public function configure(SymfonyCommand $command)
    {
        $command->addOption('--priority', null, InputOption::VALUE_REQUIRED,
            'Set a priority algorithm for the scenario execution.'
        );
    }

    /**
     * Executes controller.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return null|integer
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $prioritiser = $input->getOption('priority');

        if (!$prioritiser) {
            return;
        }

        if (!array_key_exists($prioritiser, $this->prioritisers)) {
           throw new BadPriorityException(sprintf("Priority option '%s' was not recognised", $prioritiser));
        }

        $this->exercise->setPrioritiser($this->prioritisers[$prioritiser]);
    }

    /**
     * Register a new available controller
     *
     * @param Prioritiser $prioritiser
     */
    public function registerPrioritiser(Prioritiser $prioritiser)
    {
        $this->prioritisers[$prioritiser->getName()] = $prioritiser;
    }

}
