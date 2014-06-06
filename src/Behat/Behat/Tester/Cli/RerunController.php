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
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    /**
     * @var null|string
     */
    private $cachePath;
    /**
     * @var string
     */
    private $key;
    /**
     * @var string[]
     */
    private $lines = array();

    /**
     * Initializes controller.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param null|string              $cachePath
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, $cachePath)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->cachePath = null !== $cachePath ? rtrim($cachePath, DIRECTORY_SEPARATOR) : null;
    }

    /**
     * Configures command to be executable by the controller.
     *
     * @param Command $command
     */
    public function configure(Command $command)
    {
        $command->addOption('--rerun', null, InputOption::VALUE_NONE,
            'Re-run scenarios that failed during last execution.'
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
        $this->eventDispatcher->addListener(ScenarioTested::AFTER, array($this, 'collectFailedScenario'), -50);
        $this->eventDispatcher->addListener(ExampleTested::AFTER, array($this, 'collectFailedScenario'), -50);
        $this->eventDispatcher->addListener(ExerciseCompleted::AFTER, array($this, 'writeCache'), -50);

        $this->key = $this->generateKey($input);

        if (!$input->getOption('rerun')) {
            return;
        }

        if (!$this->getFileName() || !file_exists($this->getFileName())) {
            return;
        }

        $input->setArgument('paths', $this->getFileName());
    }

    /**
     * Records scenario if it is failed.
     *
     * @param AfterScenarioTested $event
     */
    public function collectFailedScenario(AfterScenarioTested $event)
    {
        if (!$this->getFileName()) {
            return;
        }

        if ($event->getTestResult()->isPassed()) {
            return;
        }

        $feature = $event->getFeature();
        $scenario = $event->getScenario();

        $this->lines[] = $feature->getFile() . ':' . $scenario->getLine();
    }

    /**
     * Writes failed scenarios cache.
     */
    public function writeCache()
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

        file_put_contents($this->getFileName(), trim(implode("\n", $this->lines)));
    }

    /**
     * Generates cache key.
     *
     * @param InputInterface $input
     *
     * @return string
     */
    private function generateKey(InputInterface $input)
    {
        return md5(
            $input->getParameterOption(array('--profile', '-p')) .
            $input->getOption('suite') .
            implode(' ', $input->getOption('name')) .
            implode(' ', $input->getOption('tags')) .
            $input->getOption('role') .
            $input->getArgument('paths')
        );
    }

    /**
     * Returns cache filename (if exists).
     *
     * @return null|string
     */
    private function getFileName()
    {
        if (null === $this->cachePath || null === $this->key) {
            return null;
        }

        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0777);
        }

        return $this->cachePath . DIRECTORY_SEPARATOR . $this->key . '.scenarios';
    }
}
