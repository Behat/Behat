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
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Tester\Event\SuiteTested;

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
     * @var int
     */
    private $currentTestcase;
    /**
     * @var int[]
     */
    private $testcaseStats;
    /**
     * @var int[]
     */
    private $examples;

    /**
     * @param string $basePath
     */
    public function __construct($basePath)
    {
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
            SuiteTested::BEFORE     => array('startSuite', 999),
            SuiteTested::AFTER      => array('writeSuite', -50),
            FeatureTested::BEFORE   => array('startFeature', -50),
            FeatureTested::AFTER    => array('endFeature', -50),
            ScenarioTested::BEFORE  => array('startScenario', -50),
            ScenarioTested::AFTER   => array('endScenario', -50),
            StepTested::AFTER       => array('saveStep', -50),
            OutlineTested::BEFORE   => array('startOutline', -50),
            ExampleTested::BEFORE   => array('startExample', -50),
            ExampleTested::AFTER    => array('endScenario', -50),
        );
    }

    public function startSuite(SuiteTested $event)
    {
        $suite = $event->getSuite();

        $this->xml = new \SimpleXmlElement('<testsuites name="'.$suite->getName().'"></testsuites>');
    }

    public function startFeature(FeatureTested $event)
    {
        $subject = $event->getSubject();

        $this->currentTestsuite = $testsuite = $this->xml->addChild('testsuite');
        $testsuite->addAttribute('name', $subject->getTitle());
        $testsuite->addAttribute('file', str_replace('\\', '/', $this->relativizePath($subject->getFile())));

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
    }

    public function startScenario(ScenarioTested $event)
    {
        $scenario = $event->getScenario();
        $title = implode(' ', array_map(function ($l) {
            return trim($l);
        }, explode("\n", $scenario->getTitle())));

        $this->currentTestcase = $testcase = $this->currentTestsuite->addChild('testcase');
        $testcase->addAttribute('name', $title);

        $this->testcaseStats = array(
            TestResult::PASSED    => 0,
            TestResult::SKIPPED   => 0,
            TestResult::PENDING   => 0,
            TestResult::UNDEFINED => 0,
            TestResult::FAILED    => 0,
        );
    }

    public function startExample(ExampleTested $event)
    {
        $example = $event->getExample();
        if (!isset($this->examples[$this->outlineName])) {
            $this->examples[$this->outlineName] = 1;
        } else {
            $this->examples[$this->outlineName]++;
        }
        $this->currentTestcase = $testcase = $this->currentTestsuite->addChild('testcase');
        $testcase->addAttribute('name', $this->outlineName.' #'.$this->examples[$this->outlineName]);

        $this->testcaseStats = array(
            TestResult::PASSED    => 0,
            TestResult::SKIPPED   => 0,
            TestResult::PENDING   => 0,
            TestResult::UNDEFINED => 0,
            TestResult::FAILED    => 0,
        );
    }

    public function saveStep(StepTested $event)
    {
        $this->testcaseStats[$event->getResultCode()]++;
        $step = $event->getStep();

        switch ($event->getResultCode()) {
            case TestResult::PASSED:
                break;
            case TestResult::SKIPPED:
                $this->currentTestcase->addChild('skipped', $step->getType().' '.$step->getText());
                break;
            case TestResult::PENDING:
                $content = $step->getType().' '.$step->getText().': ';

                $callResult = $event->getTestResult()->getCallResult();
                if ($callResult->hasException()) {
                    $content .= $callResult->getException()->getMessage();
                }

                $this->currentTestcase->addChild('skipped', $content);
                break;
            case TestResult::UNDEFINED:
                $error = $this->currentTestcase->addChild('error');
                $error->addAttribute('type', 'undefined');
                $error->addAttribute('message', $step->getType().' '.$step->getText());
                break;
            case TestResult::FAILED:
                $content = $step->getType().' '.$step->getText().': ';

                $callResult = $event->getTestResult()->getCallResult();
                if ($callResult->hasException()) {
                    $content .= $callResult->getException()->getMessage();
                }
                $failure = $this->currentTestcase->addChild('failure');
                $failure->addAttribute('message', $content);
                break;
        }
    }

    public function endScenario()
    {
        if (0 !== $this->testcaseStats[TestResult::FAILED]) {
            $status = 'FAILED';
        } elseif (0 !== $this->testcaseStats[TestResult::PENDING]) {
            $status = 'PENDING';
        } elseif (0 !== $this->testcaseStats[TestResult::UNDEFINED]) {
            $status = 'UNDEFINED';
        } else {
            $status = 'PASSED';
        }

        $this->testsuiteStats[$status]++;

        $this->currentTestcase->addAttribute('assertions', array_sum($this->testcaseStats));
        $this->currentTestcase->addAttribute('status', $status);
    }

    public function endFeature(FeatureTested $event)
    {
        $testResult = $event->getTestResult();

        $testsuite = $this->currentTestsuite;
        $testsuite->addAttribute('tests', array_sum($this->testsuiteStats));
        $testsuite->addAttribute('failures', $this->testsuiteStats['FAILED']);
        $testsuite->addAttribute('errors', $this->testsuiteStats['PENDING'] + $this->testsuiteStats['UNDEFINED']);
    }

    public function writeSuite(SuiteTested $event)
    {
        $outputPath = $this->getParameter('output_path');
        $outputDir = $this->relativizePath($outputPath);
        if (!is_dir($outputDir)) {
            mkdir($outputDir);
        }

        $dom = new \DOMDocument('1.0');
        $dom->preserveWhitespace = false;
        $dom->formatOutput = true;
        $dom->loadXml($this->xml->asXml());
        
        file_put_contents($this->basePath.DIRECTORY_SEPARATOR.$outputDir.DIRECTORY_SEPARATOR.$event->getSuite()->getName().'.xml', $dom->saveXML());
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

    private function relativizePath($path)
    {
        return str_replace($this->basePath . DIRECTORY_SEPARATOR, '', $path);
    }
}
