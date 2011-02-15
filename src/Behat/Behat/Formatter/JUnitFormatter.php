<?php

namespace Behat\Behat\Formatter;

use Symfony\Component\EventDispatcher\EventDispatcher,
    Symfony\Component\EventDispatcher\Event;

use Behat\Gherkin\Node\FeatureNode,
    Behat\Gherkin\Node\ScenarioNode,
    Behat\Gherkin\Node\StepNode,
    Behat\Behat\Exception\FormatterException;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Progress formatter.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class JUnitFormatter extends ConsoleFormatter
{
    /**
     * Current XML filename.
     *
     * @var     string
     */
    protected $filename;
    /**
     * Test cases.
     *
     * @var     array
     */
    protected $testcases = array();
    /**
     * Total steps count.
     *
     * @var     integer
     */
    protected $stepsCount = 0;
    /**
     * Step exceptions.
     *
     * @var     array
     */
    protected $exceptions = array();
    /**
     * Feature start time.
     *
     * @var     float
     */
    protected $featureStartTime;
    /**
     * Scenario start time.
     *
     * @var     float
     */
    protected $scenarioStartTime;

    /**
     * {@inheritdoc}
     */
    protected function getDefaultParameters()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     *
     * @uses    afterFeature()
     * @uses    afterScenario()
     * @uses    afterOutlineExample()
     * @uses    afterStep()
     */
    public function registerListeners(EventDispatcher $dispatcher)
    {
        $dispatcher->connect('feature.before',          array($this, 'beforeFeature'),          -10);
        $dispatcher->connect('feature.after',           array($this, 'afterFeature'),           -10);
        $dispatcher->connect('scenario.before',         array($this, 'beforeScenario'),         -10);
        $dispatcher->connect('scenario.after',          array($this, 'afterScenario'),          -10);
        $dispatcher->connect('outline.example.before',  array($this, 'beforeOutlineExample'),   -10);
        $dispatcher->connect('outline.example.after',   array($this, 'afterOutlineExample'),    -10);
        $dispatcher->connect('step.after',              array($this, 'afterStep'),              -10);
    }

    /**
     * Listens to "feature.before" event.
     *
     * @param   Symfony\Component\EventDispatcher\Event     $event
     *
     * @uses    printTestSuiteHeader()
     */
    public function beforeFeature(Event $event)
    {
        $feature = $event->getSubject();
        $this->filename = 'TEST-' . basename($feature->getFile(), '.feature') . '.xml';

        $this->printTestSuiteHeader($feature);

        $this->stepsCount       = 0;
        $this->testcases        = array();
        $this->exceptions       = array();
        $this->featureStartTime = microtime(true);
    }

    /**
     * Listens to "feature.after" event.
     *
     * @param   Symfony\Component\EventDispatcher\Event     $event
     *
     * @uses    printTestSuiteFooter()
     * @uses    flushOutputConsole()
     */
    public function afterFeature(Event $event)
    {
        $feature    = $event->getSubject();
        $time       = microtime(true) - $this->featureStartTime;

        $this->printTestSuiteFooter($feature, $time);
        $this->flushOutputConsole();
    }

    /**
     * Listens to "scenario.before" event.
     *
     * @param   Symfony\Component\EventDispatcher\Event     $event
     */
    public function beforeScenario(Event $event)
    {
        $this->scenarioStartTime = microtime(true);
    }

    /**
     * Listens to "scenario.after" event.
     *
     * @param   Symfony\Component\EventDispatcher\Event     $event
     *
     * @uses    printTestCase()
     */
    public function afterScenario(Event $event)
    {
        $scenario   = $event->getSubject();
        $time       = microtime(true) - $this->scenarioStartTime;

        $this->printTestCase($scenario, $time);
    }

    /**
     * Listens to "outline.example.before" event.
     *
     * @param   Symfony\Component\EventDispatcher\Event     $event
     */
    public function beforeOutlineExample(Event $event)
    {
        $this->scenarioStartTime = microtime(true);
    }

    /**
     * Listens to "outline.example.after" event.
     *
     * @param   Symfony\Component\EventDispatcher\Event     $event
     *
     * @uses    printTestCase()
     */
    public function afterOutlineExample(Event $event)
    {
        $scenario   = $event->getSubject();
        $time       = microtime(true) - $this->scenarioStartTime;

        $this->printTestCase($scenario, $time);
    }

    /**
     * Listens to "step.after" event.
     *
     * @param   Symfony\Component\EventDispatcher\Event     $event
     */
    public function afterStep(Event $event)
    {
        if (null !== $event->get('exception')) {
            $this->exceptions[] = $event->get('exception');
        }

        ++$this->stepsCount;
    }

    /**
     * Prints testsuite header.
     *
     * @param   Behat\Gherkin\Node\FeatureNode  $feature
     */
    protected function printTestSuiteHeader(FeatureNode $feature)
    {
        $this->writeln('<?xml version="1.0" encoding="UTF-8"?>');
    }

    /**
     * Prints testsuite footer.
     *
     * @param   Behat\Gherkin\Node\FeatureNode  $feature
     * @param   float                           $time
     */
    protected function printTestSuiteFooter(FeatureNode $feature, $time)
    {
        $suiteStats = sprintf(
            'errors="0" failures="%d" name="%s" tests="%d" time="%f"',
            count($this->exceptions), $feature->getTitle(), $this->stepsCount, $time
        );

        $this->writeln("<testsuite $suiteStats>");
        $this->writeln(implode("\n", $this->testcases));
        $this->writeln('</testsuite>');
    }

    /**
     * Prints testcase.
     *
     * @param   Behat\Gherkin\Node\ScenarioNode $feature
     * @param   float                           $time
     */
    protected function printTestCase(ScenarioNode $scenario, $time)
    {
        $className  = $scenario->getFeature()->getTitle() . '.' . $scenario->getTitle();
        $name       = $scenario->getTitle();
        $caseStats  = sprintf(
            'clasname="%s" name="%s" time="%f"', $className, $name, $time
        );

        $xml  = "    <testcase $caseStats>\n";

        foreach ($this->exceptions as $exception) {
            $xml .= sprintf(
                '        <failure message="%s" type="%s">' . "\n",
                htmlspecialchars($exception->getMessage()),
                $this->getResultColorCode($event->get('result'))
            );
            $xml .= "<![CDATA[\n$exception\n]]>";
            $xml .= "        </failure>\n";
        }

        $xml .= "    </testcase>";

        $this->testcases[] = $xml;
    }

    /**
     * {@inheritdoc}
     */
    protected function createOutputStream()
    {
        $outputPath = $this->parameters->get('output_path');

        if (null === $outputPath) {
            throw new FormatterException(sprintf(
                'You should specify "output_path" parameter for %s', get_class($this)
            ));
        } elseif (is_file($outputPath)) {
            throw new FormatterException(sprintf(
                'Directory path expected as "output_path" parameter of %s, but got: %s',
                get_class($this),
                $outputPath
            ));
        }

        if (!is_dir($outputPath)) {
            mkdir($outputPath, 0777, true);
        }

        return fopen($outputPath . DIRECTORY_SEPARATOR . $this->filename, 'w');
    }
}
