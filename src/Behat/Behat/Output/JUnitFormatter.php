<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output;

use Behat\Behat\Tester\Result\TestResult;
use Behat\Behat\Tester\Event\StepTested;
use Behat\Behat\Tester\Event\ExampleTested;
use Behat\Behat\Tester\Event\FeatureTested;
use Behat\Behat\Tester\Event\OutlineTested;
use Behat\Behat\Tester\Event\ScenarioTested;
use Behat\Behat\Tester\Event\BackgroundTested;
use Behat\Testwork\Call\CallResults;
use Behat\Testwork\Hook\Event\LifecycleEvent;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Tester\Event\SuiteTested;
use Behat\Testwork\Exception\ExceptionPresenter;

/**
 * Behat JUnit formatter.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class JUnitFormatter implements Formatter
{
    /**
     * @var array
     */
    private $parameters = array();
    /**
     * @var ExceptionPresenter
     */
    private $exceptionPresenter;
    /**
     * @var string
     */
    private $basePath;
    /**
     * @var \SimpleXmlElement
     */
    private $xml;
    /**
     * @var \SimpleXmlElement
     */
    private $currentTestsuite;
    /**
     * @var int[]
     */
    private $testsuiteStats;
    /**
     * @var \SimpleXmlElement
     */
    private $currentTestcase;
    /**
     * @var int[]
     */
    private $testcaseStats;
    /**
     * @var int
     */
    private $exampleIndex;
    /**
     * @var string
     */
    private $outlineName;

    /**
     * @param string $basePath
     */
    public function __construct($basePath, ExceptionPresenter $exceptionPresenter)
    {
        $this->exceptionPresenter = $exceptionPresenter;

        if (null !== $basePath) {
            $realBasePath = realpath($basePath);

            if ($realBasePath) {
                $basePath = $realBasePath;
            }
        }

        $this->basePath = $basePath;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            SuiteTested::BEFORE     => array('createTestsuites', -50),
            SuiteTested::AFTER      => array('writeFile', -50),
            FeatureTested::BEFORE   => array('createTestsuite', -50),
            FeatureTested::AFTER    => array('writeTestsuite', -50),
            ScenarioTested::BEFORE  => array('startScenario', -50),
            ScenarioTested::AFTER   => array('writeTestcase', -50),
            StepTested::BEFORE      => array('startStep', -50),
            StepTested::AFTER       => array('writeStep', -50),
            OutlineTested::BEFORE   => array('startOutline', -50),
            ExampleTested::BEFORE   => array('startExample', -50),
            ExampleTested::AFTER    => array('writeTestcase', -50),
        );
    }

    public function createTestsuites(SuiteTested $event)
    {
        $suite = $event->getSuite();

        $this->xml = new \SimpleXmlElement('<testsuites></testsuites>');
        $this->xml->addAttribute('name', $suite->getName());
    }

    public function createTestsuite(FeatureTested $event)
    {
        $feature = $event->getFeature();

        $this->currentTestsuite = $testsuite = $this->xml->addChild('testsuite');
        $testsuite->addAttribute('name', $feature->getTitle());

        $this->createSystemOut($event->getHookCallResults(), $testsuite);

        $this->testsuiteStats = array(
            'PASSED' => 0,
            'UNDEFINED' => 0,
            'PENDING' => 0,
            'FAILED' => 0,
        );
    }

    public function startOutline(OutlineTested $event)
    {
        $this->outlineName = $event->getOutline()->getTitle();
        $this->exampleIndex = 0;
    }

    public function startScenario(ScenarioTested $event)
    {
        $this->createSystemOut($event->getHookCallResults(), $this->currentTestsuite);

        $title = implode(' ', array_map(function ($l) {
            return trim($l);
        }, explode("\n", $event->getScenario()->getTitle())));

        $this->createTestcase($title);
    }

    public function startExample(ExampleTested $event)
    {
        $this->createSystemOut($event->getHookCallResults(), $this->currentTestsuite);

        $this->exampleIndex++;

        $this->createTestcase($this->outlineName.' #'.$this->exampleIndex);
    }

    public function startStep(StepTested $event)
    {
        $this->createSystemOut($event->getHookCallResults(), $this->currentTestcase);
    }

    public function writeStep(StepTested $event)
    {
        $this->createSystemOut($event->getHookCallResults(), $this->currentTestcase);

        $this->testcaseStats[$event->getResultCode()]++;
        $step = $event->getStep();

        switch ($event->getResultCode()) {
            case TestResult::PASSED:
            case TestResult::SKIPPED:
                break;
            case TestResult::PENDING:
                $error = $this->currentTestcase->addChild('error', $step->getType().' '.$step->getText());
                $error->addAttribute('type', 'pending');

                $callResult = $event->getTestResult()->getCallResult();
                if ($callResult->hasException()) {
                    $error->addAttribute('message', $callResult->getException()->getMessage());
                }
                break;
            case TestResult::UNDEFINED:
                $error = $this->currentTestcase->addChild('error', $step->getType().' '.$step->getText());
                $error->addAttribute('type', 'undefined');
                break;
            case TestResult::FAILED:
                $content = $step->getType().' '.$step->getText().': ';

                $callResult = $event->getTestResult()->getCallResult();
                if ($callResult->hasException()) {
                    $content .= $this->exceptionPresenter->presentException($callResult->getException());
                }
                $failure = $this->currentTestcase->addChild('failure');
                $failure->addAttribute('message', $content);
                break;
        }

        $this->createSystemOut(new CallResults(array($event->getTestResult()->getCallResult())), $this->currentTestcase);
        
    }

    public function writeTestcase(LifecycleEvent $event)
    {
        $status = strtoupper(TestResult::codeToString($event->getResultCode()));

        $this->testsuiteStats[$status]++;

        $this->currentTestcase->addAttribute('assertions', array_sum($this->testcaseStats));
        $this->currentTestcase->addAttribute('status', $status);
    }

    public function writeTestsuite(FeatureTested $event)
    {
        $this->createSystemOut($event->getHookCallResults(), $this->currentTestsuite);

        $testResult = $event->getTestResult();

        $testsuite = $this->currentTestsuite;
        $testsuite->addAttribute('tests', array_sum($this->testsuiteStats));
        $testsuite->addAttribute('failures', $this->testsuiteStats['FAILED']);
        $testsuite->addAttribute('errors', $this->testsuiteStats['PENDING'] + $this->testsuiteStats['UNDEFINED']);
    }

    public function writeFile(SuiteTested $event)
    {
        $outputDir = $this->getParameter('output_path');
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        $dom = new \DOMDocument('1.0');
        $dom->preserveWhitespace = false;
        $dom->formatOutput = true;
        $dom->loadXml($this->xml->asXml());
        
        file_put_contents($outputDir.'/'.$this->sluggifyName($event->getSuite()->getName()).'.xml', $dom->saveXML());
    }
    
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'junit';
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription()
    {
        return 'Creates a junit xml file';
    }

    /**
     * {@inheritDoc}
     */
    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function getParameter($name)
    {
        return isset($this->parameters[$name]) ? $this->parameters[$name] : null;
    }

    /**
     * Creates testcase element.
     *
     * @param string $name
     */
    protected function createTestcase($name)
    {
        $this->currentTestcase = $testcase = $this->currentTestsuite->addChild('testcase');
        $testcase->addAttribute('name', $name);

        $this->testcaseStats = array(
            TestResult::PASSED    => 0,
            TestResult::SKIPPED   => 0,
            TestResult::PENDING   => 0,
            TestResult::UNDEFINED => 0,
            TestResult::FAILED    => 0,
        );
    }

    protected function createSystemOut(CallResults $hookCallResults, \SimpleXmlElement $node)
    {
        if (!$hookCallResults->hasStdOuts() && !$hookCallResults->hasExceptions()) {
            return;
        }

        foreach ($hookCallResults as $callResult) {
            if (!$callResult->hasStdOut() && !$callResult->hasException()) {
                continue;
            }

            $resultCode = $callResult->hasException();
            $hook = $callResult->getCall()->getCallee();

            if ($callResult->hasStdOut()) {
                $node->addChild('system-out', $callResult->getStdOut());
            }

            if ($callResult->hasException()) {
                $exception = $this->exceptionPresenter->presentException($callResult->getException());
                $node->addChild('system-out', $exception);
            }
        }
    }

    private function sluggifyName($name)
    {
        return strtolower(preg_replace('/[^[:alnum:]-_]/', '', str_replace(array(' ', "\n"), '-', $name)));
    }

    private function relativizePath($path)
    {
        return str_replace($this->basePath . DIRECTORY_SEPARATOR, '', $path);
    }
}
