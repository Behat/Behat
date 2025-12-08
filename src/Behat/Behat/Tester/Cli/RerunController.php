<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester\Cli;

use Behat\Behat\EventDispatcher\Event\AfterScenarioTested;
use Behat\Behat\EventDispatcher\Event\ExampleTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Testwork\Cli\Controller;
use Behat\Testwork\EventDispatcher\Event\ExerciseCompleted;
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
    /**
     * @var string|null
     */
    private $cachePath;
    /**
     * @var string
     */
    private $key;
    /**
     * @var array<string, string[]>
     */
    private $lines = [];

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

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->eventDispatcher->addListener(ScenarioTested::AFTER, $this->collectFailedScenario(...), -50);
        $this->eventDispatcher->addListener(ExampleTested::AFTER, $this->collectFailedScenario(...), -50);
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

        if ($this->resultInterpreter->interpretResult($event->getTestResult()) === ResultInterpreter::PASS) {
            return;
        }

        $feature = $event->getFeature();
        $scenario = $event->getScenario();
        $suitename = $event->getSuite()->getName();

        $this->lines[$suitename][] = $feature->getFile() . ':' . $scenario->getLine();
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
     *
     * @return string|null
     */
    private function getFileName()
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
