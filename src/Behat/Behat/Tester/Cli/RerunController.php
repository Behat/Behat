<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester\Cli;

use Behat\Behat\EventDispatcher\Event\AfterFeatureTested;
use Behat\Behat\EventDispatcher\Event\AfterScenarioTested;
use Behat\Behat\EventDispatcher\Event\ExampleTested;
use Behat\Behat\EventDispatcher\Event\FeatureTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Testwork\Cli\Controller;
use Behat\Testwork\EventDispatcher\Event\AfterSuiteTested;
use Behat\Testwork\EventDispatcher\Event\ExerciseCompleted;
use Behat\Testwork\EventDispatcher\Event\SuiteTested;
use Behat\Testwork\Tester\Result\ResultInterpreter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Caches failed scenarios and reruns only them if `--rerun` option provided.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class RerunController implements Controller
{
    private readonly ?string $cachePath;
    private ?string $key = null;
    /**
     * @var array<string, list<string>>
     */
    private array $lines = [];
    /**
     * Feature files run by each suite, so that a failing after-feature or after-suite hook can
     * queue them for the re-run even though no single scenario is to blame.
     *
     * @var array<string, list<string>>
     */
    private array $features = [];

    /**
     * Initializes controller.
     *
     * @param string|null $cachePath
     */
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ResultInterpreter $resultInterpreter,
        private readonly string $basepath,
        $cachePath,
    ) {
        $this->cachePath = null !== $cachePath ? rtrim($cachePath, DIRECTORY_SEPARATOR) : null;
    }

    /**
     * Configures command to be executable by the controller.
     */
    public function configure(Command $command): void
    {
        $command->addOption(
            '--rerun',
            null,
            InputOption::VALUE_NONE,
            'Re-run scenarios that failed during last execution, or run everything if there were no failures.'
        );
        $command->addOption(
            '--rerun-only',
            null,
            InputOption::VALUE_NONE,
            'Re-run scenarios that failed during last execution, or exit if there were no failures.'
        );
    }

    public function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $this->eventDispatcher->addListener(ScenarioTested::AFTER, $this->collectFailedScenario(...), -50);
        $this->eventDispatcher->addListener(ExampleTested::AFTER, $this->collectFailedScenario(...), -50);
        $this->eventDispatcher->addListener(FeatureTested::AFTER, $this->collectFailedFeature(...), -50);
        $this->eventDispatcher->addListener(SuiteTested::AFTER, $this->collectFailedSuite(...), -50);
        $this->eventDispatcher->addListener(ExerciseCompleted::AFTER, $this->writeCache(...), -50);

        $this->key = $this->generateKey($input);

        if (!$input->getOption('rerun') && !$input->getOption('rerun-only')) {
            return null;
        }

        if (!$this->getFileName() || !file_exists($this->getFileName())) {
            if ($input->getOption('rerun-only')) {
                $output->writeln('No failure found, exiting.');

                return 0;
            }

            return null;
        }

        $input->setArgument('paths', [$this->getFileName()]);

        return null;
    }

    /**
     * Records scenario if it is failed.
     */
    public function collectFailedScenario(AfterScenarioTested $event): void
    {
        if (!$this->getFileName()) {
            return;
        }

        // A scenario whose steps all passed can still fail through an after-scenario hook: that
        // failure lives in the teardown, not in the test result, and must be re-run all the same.
        if ($this->resultInterpreter->interpretResult($event->getTestResult()) === ResultInterpreter::PASS
            && $event->getTeardown()->isSuccessful()
        ) {
            return;
        }

        $this->addPath(
            $event->getSuite()->getName(),
            $event->getFeature()->getFile() . ':' . $event->getScenario()->getLine()
        );
    }

    /**
     * Records the whole feature if its after-feature hook failed.
     *
     * No single scenario is responsible, so the feature is re-run as a whole, which is also what
     * gives the hook the same chance to run again.
     */
    public function collectFailedFeature(AfterFeatureTested $event): void
    {
        if (!$this->getFileName()) {
            return;
        }

        $suitename = $event->getSuite()->getName();
        $file = $event->getFeature()->getFile();

        if (null !== $file && !in_array($file, $this->features[$suitename] ?? [], true)) {
            $this->features[$suitename][] = $file;
        }

        if ($event->getTeardown()->isSuccessful()) {
            return;
        }

        $this->addPath($suitename, $file);
    }

    /**
     * Records every feature the suite ran if its after-suite hook failed.
     *
     * The hook belongs to one suite, so only that suite is re-run, and the other ones are left
     * alone.
     */
    public function collectFailedSuite(SuiteTested $event): void
    {
        // A suite stopped by --stop-on-failure is dispatched under the same name as an
        // AfterSuiteAborted, which ran no teardown to look at.
        if (!$event instanceof AfterSuiteTested) {
            return;
        }

        if (!$this->getFileName() || $event->getTeardown()->isSuccessful()) {
            return;
        }

        $suitename = $event->getSuite()->getName();

        foreach ($this->features[$suitename] ?? [] as $file) {
            $this->addPath($suitename, $file);
        }
    }

    /**
     * Queues a path for the re-run of a suite, keeping it free of duplicates and of paths already
     * covered by a whole feature file.
     */
    private function addPath(string $suitename, ?string $path): void
    {
        if (null === $path) {
            return;
        }

        $lines = $this->lines[$suitename] ?? [];

        // A whole feature file supersedes the scenarios of that file which are already queued.
        if (!str_contains(basename($path), ':')) {
            $lines = array_values(array_filter(
                $lines,
                static fn (string $line): bool => $line !== $path && !str_starts_with($line, $path . ':')
            ));
        } elseif (in_array(substr($path, 0, (int) strrpos($path, ':')), $lines, true)) {
            return;
        }

        if (!in_array($path, $lines, true)) {
            $lines[] = $path;
        }

        $this->lines[$suitename] = $lines;
    }

    /**
     * Writes failed scenarios cache.
     */
    public function writeCache(): void
    {
        if (!$this->getFileName()) {
            return;
        }

        if (file_exists($this->getFileName())) {
            unlink($this->getFileName());
        }

        if (0 === count($this->lines)) {
            return;
        }

        file_put_contents($this->getFileName(), json_encode($this->lines));
    }

    /**
     * Generates cache key.
     */
    private function generateKey(InputInterface $input): string
    {
        return md5(
            $input->getParameterOption(['--profile', '-p']) .
            $input->getOption('suite') .
            implode(' ', $input->getOption('name')) .
            implode(' ', $input->getOption('tags')) .
            $input->getOption('role') .
            \implode('', $input->getArgument('paths')) .
            $this->basepath
        );
    }

    /**
     * Returns cache filename (if exists).
     */
    private function getFileName(): ?string
    {
        if (null === $this->cachePath || null === $this->key) {
            return null;
        }

        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0777);
        }

        return $this->cachePath . DIRECTORY_SEPARATOR . $this->key . '.rerun';
    }
}
