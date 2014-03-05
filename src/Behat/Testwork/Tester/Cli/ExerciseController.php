<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester\Cli;

use Behat\Testwork\Cli\Controller;
use Behat\Testwork\Specification\SpecificationFinder;
use Behat\Testwork\Specification\SpecificationIterator;
use Behat\Testwork\Suite\Suite;
use Behat\Testwork\Suite\SuiteRepository;
use Behat\Testwork\Tester\Exercise;
use Behat\Testwork\Tester\Result\Interpretation\StrictInterpretation;
use Behat\Testwork\Tester\Result\ResultInterpreter;
use Behat\Testwork\Tester\Result\TestResult;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Testwork exercise controller.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ExerciseController implements Controller
{
    /**
     * @var SuiteRepository
     */
    private $suiteRepository;
    /**
     * @var SpecificationFinder
     */
    private $specificationFinder;
    /**
     * @var Exercise
     */
    private $exercise;
    /**
     * @var ResultInterpreter
     */
    private $resultInterpreter;
    /**
     * @var Boolean
     */
    private $strict;
    /**
     * @var Boolean
     */
    private $skip;

    /**
     * Initializes controller.
     *
     * @param SuiteRepository     $suiteRepository
     * @param SpecificationFinder $specificationFinder
     * @param Exercise            $exercise
     * @param ResultInterpreter   $resultInterpreter
     * @param Boolean             $strict
     * @param Boolean             $skip
     */
    public function __construct(
        SuiteRepository $suiteRepository,
        SpecificationFinder $specificationFinder,
        Exercise $exercise,
        ResultInterpreter $resultInterpreter,
        $strict = false,
        $skip = false
    ) {
        $this->suiteRepository = $suiteRepository;
        $this->specificationFinder = $specificationFinder;
        $this->exercise = $exercise;
        $this->resultInterpreter = $resultInterpreter;
        $this->strict = $strict;
        $this->skip = $skip;
    }

    /**
     * Configures command to be executable by the controller.
     *
     * @param Command $command
     */
    public function configure(Command $command)
    {
        $command
            ->addArgument(
                'locator',
                InputArgument::OPTIONAL,
                'Optional path to a specific test.'
            )
            ->addOption(
                '--strict',
                null,
                InputOption::VALUE_NONE,
                'Passes only if all tests are explicitly passing.'
            )
            ->addOption(
                '--dry-run',
                null,
                InputOption::VALUE_NONE,
                'Invokes formatters without executing the steps & hooks.'
            );
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
        if ($this->strict || $input->getOption('strict')) {
            $this->resultInterpreter->registerResultInterpretation(new StrictInterpretation());
        }

        $specs = $this->findSpecifications($input);
        $result = $this->testSpecifications($input, $specs);

        return $this->resultInterpreter->interpretResult($result);
    }

    /**
     * Finds exercise specifications.
     *
     * @param InputInterface $input
     *
     * @return SpecificationIterator[]
     */
    protected function findSpecifications(InputInterface $input)
    {
        return $this->findSuitesSpecifications($this->getAvailableSuites(), $input->getArgument('locator'));
    }

    /**
     * Tests exercise specifications.
     *
     * @param InputInterface          $input
     * @param SpecificationIterator[] $specifications
     *
     * @return TestResult
     */
    protected function testSpecifications(InputInterface $input, array $specifications)
    {
        $skip = $input->getOption('dry-run') || $this->skip;

        $skip = $skip || $this->exercise->setUp($specifications, $skip);
        $testResult = $this->exercise->test($specifications, $skip);
        $this->exercise->tearDown($specifications, $skip, $testResult);

        return new TestResult($testResult->getResultCode());
    }

    /**
     * Returns all currently available suites.
     *
     * @return Suite[]
     */
    protected function getAvailableSuites()
    {
        return $this->suiteRepository->getSuites();
    }

    /**
     * Finds specification iterators for all provided suites using locator.
     *
     * @param Suite[]     $suites
     * @param null|string $locator
     *
     * @return SpecificationIterator[]
     */
    protected function findSuitesSpecifications($suites, $locator)
    {
        return $this->specificationFinder->findSuitesSpecifications($suites, $locator);
    }
}
