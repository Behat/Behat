<?php

namespace Behat\Behat\Output\Formatter;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Event\EventInterface;
use Behat\Behat\Event\ExampleEvent;
use Behat\Behat\Event\FeatureEvent;
use Behat\Behat\Event\ScenarioEvent;
use Behat\Behat\Event\StepEvent;
use Behat\Behat\Hook\Callee\ScenarioHook;
use Behat\Behat\Hook\Callee\StepHook;
use Behat\Behat\Hook\Event\HookEvent;
use DOMDocument;
use Exception;
use InvalidArgumentException;

/**
 * Progress formatter.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class JunitFormatter extends CliFormatter
{
    /**
     * @var string
     */
    private $filename;
    /**
     * @var integer
     */
    private $failures;
    /**
     * @var integer
     */
    private $tests;
    /**
     * @var integer
     */
    private $skipped;
    /**
     * @var float
     */
    private $time;
    /**
     * @var string[]
     */
    private $testCases = array();
    /**
     * @var string
     */
    private $stdout;
    /**
     * @var integer
     */
    private $scenarioStartTime = 0;
    /**
     * @var string
     */
    private $scenarioExceptionCause;
    /**
     * @var Exception
     */
    private $scenarioException;
    /**
     * @var string
     */
    private $scenarioStdout;

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            EventInterface::BEFORE_FEATURE  => array('prepareTestSuite', -50),
            EventInterface::AFTER_FEATURE   => array('writeTestSuite'),
            EventInterface::BEFORE_SCENARIO => array('prepareScenarioOrExample', -50),
            EventInterface::BEFORE_EXAMPLE  => array('prepareScenarioOrExample', -50),
            EventInterface::AFTER_SCENARIO  => array('createScenarioTestCase', -50),
            EventInterface::AFTER_EXAMPLE   => array('createExampleTestCase', -50),
            EventInterface::AFTER_STEP      => array('captureStepExceptionAndStdout', -50),
            EventInterface::AFTER_HOOK      => array('captureHookExceptionAndStdout', -50),
        );
    }

    /**
     * Returns formatter name.
     *
     * @return string
     */
    public function getName()
    {
        return 'junit';
    }

    /**
     * Returns formatter description.
     *
     * @return string
     */
    public function getDescription()
    {
        return 'Generates a report similar to Ant+JUnit.';
    }

    /**
     * Prepares test suite report for feature.
     *
     * @param FeatureEvent $event
     */
    public function prepareTestSuite(FeatureEvent $event)
    {
        $feature = $event->getFeature();
        $replace = array(
            $this->getParameter('features_path') => '',
            DIRECTORY_SEPARATOR => '-',
            '.feature' => '.xml'
        );

        $this->filename = 'TEST' . strtr($feature->getFile(), $replace);
        $this->failures = $this->tests = $this->skipped = $this->time = 0;
        $this->testCases = array();
        $this->stdout = '';
    }

    /**
     * Writes test suite to the filesystem.
     *
     * @param FeatureEvent $event
     */
    public function writeTestSuite(FeatureEvent $event)
    {
        $feature = $event->getFeature();

        $testSuite = '<?xml version="1.0" encoding="UTF-8"?>';
        $testSuite .= sprintf('<testsuite name="%s" tests="%d" failures="%d" skipped="%d" time="%.6f">',
            $feature->getTitle() ? $feature->getTitle() : basename($feature->getFile(), '.feature'),
            $this->tests,
            $this->failures,
            $this->skipped,
            $this->time
        );

        foreach ($this->testCases as $testCase) {
            $testSuite .= $testCase;
        }

        if ('' !== $this->stdout) {
            $testSuite .= sprintf('<system-out><![CDATA[%s]]></system-out>', trim($this->stdout));
        }

        $testSuite .= '</testsuite>';

        $dom = new DOMDocument;
        $dom->preserveWhiteSpace = false;
        @$dom->loadXML(trim($testSuite));
        $dom->formatOutput = true;

        $this->writeln($dom->saveXml());

        $this->flushOutputConsole();
    }

    /**
     * Prepares scenario or example statistics collectors.
     */
    public function prepareScenarioOrExample()
    {
        $this->scenarioStartTime = microtime(true);
        $this->scenarioException = null;
        $this->scenarioExceptionCause = '';
        $this->scenarioStdout = '';
    }

    /**
     * Captures exception and stdout from step.
     *
     * @param StepEvent $event
     */
    public function captureStepExceptionAndStdout(StepEvent $event)
    {
        $step = $event->getStep();

        if ($event->hasException()) {
            $this->scenarioException = $event->getException();
            $this->scenarioExceptionCause = sprintf('%s %s # %s:%d',
                $step->getType(),
                $step->getText(),
                $step->getFile(),
                $step->getLine()
            );
        }

        if ($event->hasStdOut()) {
            $this->scenarioStdout .= sprintf("%s %s # %s:%d\n%s\n\n",
                $step->getType(),
                $step->getText(),
                $step->getFile(),
                $step->getLine(),
                $event->getStdOut()
            );
        }
    }

    /**
     * Captures exception and stdout from hook.
     *
     * @param HookEvent $event
     */
    public function captureHookExceptionAndStdout(HookEvent $event)
    {
        $hook = $event->getHook();

        if ($event->hasException()) {
            $this->scenarioException = $event->getException();
            $this->scenarioExceptionCause = sprintf('`%s` hook # %s',
                $hook->toString(),
                $hook->getPath()
            );
        }

        if ($event->hasStdOut()) {
            $stdout = sprintf("%s hook # %s\n%s\n\n",
                $hook->toString(),
                $hook->getPath(),
                $event->getStdOut()
            );

            if ($hook instanceof ScenarioHook || $hook instanceof StepHook) {
                $this->scenarioStdout .= $stdout;
            } else {
                $this->stdout .= $stdout;
            }
        }
    }

    /**
     * Creates scenario test case.
     *
     * @param ScenarioEvent $event
     */
    public function createScenarioTestCase(ScenarioEvent $event)
    {
        $scenario = $event->getScenario();
        $feature = $scenario->getFeature();
        $duration = microtime(true) - $this->scenarioStartTime;

        $testCase = sprintf('<testcase classname="%s" name="%s" time="%.6f">',
            $feature->getTitle() ? $feature->getTitle() : basename($feature->getFile(), '.feature'),
            $scenario->getTitle() ? $scenario->getTitle() : sprintf('At line %d', $scenario->getLine()),
            $duration
        );

        $this->tests += 1;
        switch ($event->getStatus()) {
            case StepEvent::SKIPPED:
            case StepEvent::PENDING:
            case StepEvent::UNDEFINED:
                $testCase .= '<skipped></skipped>';
                $this->skipped += 1;
                break;
            case StepEvent::FAILED:
                $testCase .= sprintf('<failure type="%s" message="%s"><![CDATA[%s]]></failure>',
                    get_class($this->scenarioException),
                    $this->scenarioException->getMessage(),
                    "\n" . $this->scenarioExceptionCause . "\n". $this->scenarioException
                );
                $this->failures += 1;
                break;
        }

        if ('' !== $this->scenarioStdout) {
            $testCase .= sprintf('<system-out><![CDATA[%s]]></system-out>', trim($this->scenarioStdout));
        }

        $testCase .= '</testcase>';

        $this->time += $duration;
        $this->testCases[] = $testCase;
    }

    /**
     * Creates outline example test case.
     *
     * @param ExampleEvent $event
     */
    public function createExampleTestCase(ExampleEvent $event)
    {
        $example = $event->getExample();
        $feature = $example->getOutline()->getFeature();
        $duration = microtime(true) - $this->scenarioStartTime;

        $testCase = sprintf('<testcase classname="%s" name="%s" time="%.6f">',
            $feature->getTitle() ? $feature->getTitle() : basename($feature->getFile(), '.feature'),
            sprintf('Example %s', $example->getTitle()),
            $duration
        );

        $this->tests += 1;
        switch ($event->getStatus()) {
            case StepEvent::SKIPPED:
            case StepEvent::PENDING:
            case StepEvent::UNDEFINED:
                $testCase .= '<skipped></skipped>';
                $this->skipped += 1;
                break;
            case StepEvent::FAILED:
                $testCase .= sprintf('<failure type="%s" message="%s"><![CDATA[%s]]></failure>',
                    get_class($this->scenarioException),
                    $this->scenarioException->getMessage(),
                    "\n" . $this->scenarioExceptionCause . "\n" . $this->scenarioException
                );
                $this->failures += 1;
                break;
        }

        if ('' !== $this->scenarioStdout) {
            $testCase .= sprintf('<system-out><![CDATA[%s]]></system-out>', trim($this->scenarioStdout));
        }

        $testCase .= '</testcase>';

        $this->time += $duration;
        $this->testCases[] = $testCase;
    }

    /**
     * Returns default parameters to construct ParameterBag.
     *
     * @return array
     */
    protected function getDefaultParameters()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    protected function createOutputStream()
    {
        $outputPath = $this->getParameter('output_path');

        if (null === $outputPath) {
            throw new InvalidArgumentException(sprintf(
                'You should specify "output_path" parameter for %s formatter.', $this->getName()
            ));
        }

        if (is_file($outputPath)) {
            throw new InvalidArgumentException(sprintf(
                'Directory path expected as "output_path" parameter of %s formatter, but got: %s',
                $this->getName(),
                $outputPath
            ));
        }

        if (!is_dir($outputPath)) {
            mkdir($outputPath, 0777, true);
        }

        return fopen($outputPath . DIRECTORY_SEPARATOR . $this->filename, 'w');
    }
}
