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
use Behat\Testwork\Tester\Exception\WrongPathsException;
use Behat\Testwork\Tester\Exercise;
use Behat\Testwork\Tester\Result\IntegerTestResult;
use Behat\Testwork\Tester\Result\ResultInterpreter;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Result\TestResults;
use Behat\Testwork\Tester\Result\TestWithSetupResult;
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
     * Initializes controller.
     *
     * @param bool             $skip
     */
    public function __construct(
        private readonly SuiteRepository $suiteRepository,
        private readonly SpecificationFinder $specificationFinder,
        private readonly Exercise $exercise,
        private readonly ResultInterpreter $resultInterpreter,
        private $skip = false,
    ) {
    }

    public function configure(Command $command): void
    {
        $locatorsExamples = implode(PHP_EOL, array_map(
            fn ($locator): string => '- ' . $locator,
            $this->specificationFinder->getExampleLocators()
        ));

        $command
            ->addArgument(
                'paths',
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'Optional path(s) to execute. Could be:' . PHP_EOL . $locatorsExamples,
            )
            ->addOption(
                '--dry-run',
                null,
                InputOption::VALUE_NONE,
                'Invokes formatters without executing the tests and hooks.'
            )
            ->addOption(
                '--allow-no-tests',
                null,
                InputOption::VALUE_NONE,
                'Will not fail if no specifications are found.'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $paths = $this->extractUniquePaths($input);
        $specs = $this->findSpecifications($paths);
        $result = $this->testSpecifications($input, $specs);

        if ($paths !== null && !$input->getOption('allow-no-tests') && TestResults::NO_TESTS === $result->getResultCode()) {
            throw new WrongPathsException(
                sprintf(
                    'No specifications found at path(s) `%s`. This might be because of incorrect paths configuration in your `suites`.',
                    implode(', ', $paths)
                ),
                $paths
            );
        }

        return $this->resultInterpreter->interpretResult($result);
    }

    /**
     * Finds exercise specifications.
     *
     * @param list<string>|null $paths
     *
     * @return SpecificationIterator[]
     */
    private function findSpecifications(?array $paths): array
    {
        $availableSuites = $this->getAvailableSuites();
        if ($paths === null) {
            return $this->findSuitesSpecifications($availableSuites, null);
        }

        $specifications = [];

        foreach ($paths as $path) {
            $specifications = array_merge($specifications, $this->findSuitesSpecifications($availableSuites, $path));
        }

        return $specifications;
    }

    /**
     * Tests exercise specifications.
     *
     * @param SpecificationIterator[] $specifications
     */
    private function testSpecifications(InputInterface $input, array $specifications): TestResult
    {
        $skip = $input->getOption('dry-run') || $this->skip;

        $setup = $this->exercise->setUp($specifications, $skip);
        $skip = !$setup->isSuccessful() || $skip;
        $testResult = $this->exercise->test($specifications, $skip);
        $teardown = $this->exercise->tearDown($specifications, $skip, $testResult);

        $result = new IntegerTestResult($testResult->getResultCode());

        return new TestWithSetupResult($setup, $result, $teardown);
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
     *
     * @return SpecificationIterator[]
     */
    private function findSuitesSpecifications(array $suites, ?string $locator): array
    {
        return $this->specificationFinder->findSuitesSpecifications($suites, $locator);
    }

    /**
     * Extracts unique paths from input argument. Returns null if no paths were supplied.
     */
    private function extractUniquePaths(InputInterface $input): ?array
    {
        $paths = $input->getArgument('paths') ?: null;
        if ($paths === null) {
            return null;
        }

        return array_values(array_unique($paths));
    }
}
