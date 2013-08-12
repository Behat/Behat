<?php

namespace Behat\Behat\Console\Processor;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Event\EventInterface;
use Behat\Behat\Event\StepEvent;
use Behat\Behat\EventDispatcher\DispatchingService;
use Behat\Behat\Features\Event\FeaturesCarrierEvent;
use Behat\Behat\Features\SuitedFeature;
use Behat\Behat\Tester\Event\ExerciseTesterCarrierEvent;
use Behat\Behat\Tester\ExerciseTester;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Run configuration processor.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class RunProcessor extends DispatchingService implements ProcessorInterface
{
    /**
     * @var Boolean
     */
    private $strict;

    /**
     * Initializes processor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param Boolean                  $strict
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, $strict = false)
    {
        parent::__construct($eventDispatcher);

        $this->strict = $strict;
    }

    /**
     * Configures command to be able to process it later.
     *
     * @param Command $command
     */
    public function configure(Command $command)
    {
        $command
            ->addArgument('features', InputArgument::OPTIONAL,
                "Feature(s) to run. Could be:" . PHP_EOL .
                "- a dir <comment>(features/)</comment>" . PHP_EOL .
                "- a feature <comment>(*.feature)</comment>" . PHP_EOL .
                "- a scenario at specific line <comment>(*.feature:10)</comment>." . PHP_EOL .
                "- all scenarios at or after a specific line <comment>(*.feature:10-*)</comment>." . PHP_EOL .
                "- all scenarios at a line within a specific range <comment>(*.feature:10-20)</comment>."
            )
            ->addOption('--strict', null, InputOption::VALUE_NONE,
                'Fail if there are any undefined or pending steps.'
            )
            ->addOption('--suite', null, InputOption::VALUE_REQUIRED,
                'Only execute features that belong to given suite.'
            );
    }

    /**
     * Processes data from container and console input.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return integer
     */
    public function process(InputInterface $input, OutputInterface $output)
    {
        $suiteName = $input->getOption('suite');

        $features = array();
        foreach ($this->getLocators($input) as $locator) {
            $features = array_merge($features, $this->getFeatures($locator, $suiteName));
        }

        $result = $this->getExerciseTester()->test($features);

        if ($this->strict || $input->getOption('strict')) {
            return intval(StepEvent::PASSED < $result);
        }

        return intval(StepEvent::FAILED === $result);
    }

    /**
     * Returns priority of the processor in which it should be configured and executed.
     *
     * @return integer
     */
    public function getPriority()
    {
        return 0;
    }

    /**
     * Gets feature locators from input.
     *
     * @param InputInterface $input
     *
     * @return string[]
     */
    private function getLocators(InputInterface $input)
    {
        $featuresLocator = $input->getArgument('features');

        if (is_file($featuresLocator) && 'scenarios' === pathinfo($featuresLocator, PATHINFO_EXTENSION)) {
            return explode("\n", trim(file_get_contents($featuresLocator)));
        }

        return array($featuresLocator);
    }

    /**
     * Loads features for appropriate suite and locator if specified or all if not.
     *
     * @param null|string $locator
     * @param null|string $suiteName
     *
     * @return SuitedFeature[]
     */
    private function getFeatures($locator = null, $suiteName = null)
    {
        $featuresProvider = new FeaturesCarrierEvent($locator, $suiteName);
        $this->dispatch(EventInterface::LOAD_FEATURES, $featuresProvider);

        return $featuresProvider->getFeatures();
    }

    /**
     * Returns exercise tester instance.
     *
     * @return ExerciseTester
     *
     * @throws RuntimeException
     */
    private function getExerciseTester()
    {
        $testerProvider = new ExerciseTesterCarrierEvent();

        $this->dispatch(EventInterface::CREATE_EXERCISE_TESTER, $testerProvider);
        if (!$testerProvider->hasTester()) {
            throw new RuntimeException('Can not find exercise tester.');
        }

        return $testerProvider->getTester();
    }
}
