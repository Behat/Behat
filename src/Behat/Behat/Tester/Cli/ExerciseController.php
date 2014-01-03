<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester\Cli;

use Behat\Testwork\Subject\SubjectIterator;
use Behat\Testwork\Tester\Cli\ExerciseController as BaseController;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Behat exercise controller.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ExerciseController extends BaseController
{
    /**
     * Configures command to be executable by the controller.
     *
     * @param Command $command
     */
    public function configure(Command $command)
    {
        $command
            ->addArgument(
                'features', InputArgument::OPTIONAL,
                "Feature(s) to run. Could be:" . PHP_EOL .
                "- a dir <comment>(features/)</comment>" . PHP_EOL .
                "- a feature <comment>(*.feature)</comment>" . PHP_EOL .
                "- a scenario at specific line <comment>(*.feature:10)</comment>." . PHP_EOL .
                "- all scenarios at or after a specific line <comment>(*.feature:10-*)</comment>." . PHP_EOL .
                "- all scenarios at a line within a specific range <comment>(*.feature:10-20)</comment>."
            )
            ->addOption(
                '--strict', null, InputOption::VALUE_NONE,
                'Fail if there are any undefined or pending steps.'
            )
            ->addOption(
                '--dry-run', null, InputOption::VALUE_NONE,
                'Invokes formatters without executing the steps & hooks.'
            );
    }

    /**
     * Creates exercise subject iterators.
     *
     * @param InputInterface $input
     *
     * @return SubjectIterator[]
     */
    protected function findSubjects(InputInterface $input)
    {
        $subjects = array();
        foreach ($this->getFeatureLocators($input) as $locator) {
            $subjects = array_merge($subjects, $this->findSuitesSubjects($this->getAvailableSuites(), $locator));
        }

        return $subjects;
    }

    /**
     * Gets feature locators from input.
     *
     * @param InputInterface $input
     *
     * @return string[]
     */
    private function getFeatureLocators(InputInterface $input)
    {
        $featuresLocator = $input->getArgument('features');

        if (is_file($featuresLocator) && 'scenarios' === pathinfo($featuresLocator, PATHINFO_EXTENSION)) {
            return explode("\n", trim(file_get_contents($featuresLocator)));
        }

        return array($featuresLocator);
    }
}
