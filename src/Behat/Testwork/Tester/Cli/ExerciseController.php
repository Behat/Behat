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
use Behat\Testwork\Tester\Context\ExerciseContext;
use Behat\Testwork\Tester\Control\BasicRunControl;
use Behat\Testwork\Tester\Exception\WrongPathsException;
use Behat\Testwork\Tester\Exercise;
use Behat\Testwork\Tester\Result\ResultInterpreter;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Result\TestResults;
use Behat\Testwork\Tester\Tester;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Executes exercise.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ExerciseController implements Controller
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
     * @var Tester
     */
    private $exerciseTester;
    /**
     * @var ResultInterpreter
     */
    private $resultInterpreter;
    /**
     * @var Boolean
     */
    private $skip;

    /**
     * Initializes controller.
     *
     * @param SuiteRepository     $suiteRepository
     * @param SpecificationFinder $specificationFinder
     * @param Tester              $exerciseTester
     * @param ResultInterpreter   $resultInterpreter
     * @param Boolean             $skip
     */
    public function __construct(
        SuiteRepository $suiteRepository,
        SpecificationFinder $specificationFinder,
        Tester $exerciseTester,
        ResultInterpreter $resultInterpreter,
        $skip = false
    ) {
        $this->suiteRepository = $suiteRepository;
        $this->specificationFinder = $specificationFinder;
        $this->exerciseTester = $exerciseTester;
        $this->resultInterpreter = $resultInterpreter;
        $this->skip = $skip;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(Command $command)
    {
        $locatorsExamples = implode(PHP_EOL, array_map(
            function ($locator) { return '- ' . $locator; },
            $this->specificationFinder->getExampleLocators()
        ));

        $command
            ->addArgument('paths', InputArgument::OPTIONAL,
                'Optional path(s) to execute. Could be:' . PHP_EOL . $locatorsExamples
            )
            ->addOption('--dry-run', null, InputOption::VALUE_NONE,
                'Invokes formatters without executing the tests and hooks.'
            );
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $specs = $this->findSpecifications($input);
        $result = $this->testSpecifications($input, $specs);

        if ($input->getArgument('paths') && TestResults::NO_TESTS === $result->getResultCode()) {
            throw new WrongPathsException(
                sprintf('No specifications found at path(s) `%s`.', $input->getArgument('paths')),
                $input->getArgument('paths')
            );
        }

        return $this->resultInterpreter->interpretResult($result);
    }

    /**
     * Finds exercise specifications.
     *
     * @param InputInterface $input
     *
     * @return SpecificationIterator[]
     */
    private function findSpecifications(InputInterface $input)
    {
        return $this->findSuitesSpecifications(
            $this->getAvailableSuites(),
            $input->getArgument('paths')
        );
    }

    /**
     * Tests exercise specifications.
     *
     * @param InputInterface          $input
     * @param SpecificationIterator[] $specifications
     *
     * @return TestResult
     */
    private function testSpecifications(InputInterface $input, array $specifications)
    {
        $context = new ExerciseContext($specifications);
        $control = $input->getOption('dry-run') || $this->skip
            ? BasicRunControl::skipAll()
            : BasicRunControl::runAll();

        return $this->exerciseTester->test($context, $control);
    }

    /**
     * Returns all currently available suites.
     *
     * @return Suite[]
     */
    private function getAvailableSuites()
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
    private function findSuitesSpecifications($suites, $locator)
    {
        return $this->specificationFinder->findSuitesSpecifications($suites, $locator);
    }
}
