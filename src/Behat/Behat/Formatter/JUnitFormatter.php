<?php

namespace Behat\Behat\Formatter;

use Behat\Behat\Event\EventInterface,
    Behat\Behat\Event\FeatureEvent,
    Behat\Behat\Event\ScenarioEvent,
    Behat\Behat\Event\OutlineExampleEvent,
    Behat\Behat\Event\StepEvent;

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
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class JUnitFormatter extends ConsoleFormatter
{
    /**
     * Current XML filename.
     *
     * @var string
     */
    protected $filename;
    /**
     * Test cases.
     *
     * @var array
     */
    protected $testcases = array();
    /**
     * Total steps count.
     *
     * @var integer
     */
    protected $stepsCount = 0;
    /**
     * Total scenarios count.
     *
     * @var integer
     */
    protected $scenariosCount = 0;
    /**
     * Total scenarios count.
     *
     * @var integer
     */
    protected $scenarioStepsCount = 0;
    /**
     * Total exceptions count.
     *
     * @var integer
     */
    protected $exceptionsCount = 0;
    /**
     * Total failure count.
     *
     * @var integer
     */
    protected $failureCount = 0;
    /**
     * Total pending count.
     *
     * @var integer
     */
    protected $pendingCount = 0;
    /**
     * Step exceptions.
     *
     * @var array
     */
    protected $exceptions = array();
    /**
     * Feature start time.
     *
     * @var float
     */
    protected $featureStartTime;
    /**
     * Scenario start time.
     *
     * @var float
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
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        $events = array(
            'beforeFeature', 'afterFeature', 'beforeScenario', 'afterScenario',
            'beforeOutlineExample', 'afterOutlineExample', 'afterStep'
        );

        return array_combine($events, $events);
    }

    /**
     * Listens to "feature.before" event.
     *
     * @param FeatureEvent $event
     *
     * @uses printTestSuiteHeader()
     */
    public function beforeFeature(FeatureEvent $event)
    {
        $feature = $event->getFeature();
        $replace = array(
            realpath($this->getParameter('features_path')) => '',
            DIRECTORY_SEPARATOR => '-',
            '.feature' => '.xml'
        );
        $this->filename = 'TEST' . strtr($feature->getFile(), $replace);

        $this->printTestSuiteHeader($feature);

        $this->stepsCount       = 0;
        $this->testcases        = array();
        $this->exceptionsCount  = 0;
        $this->failureCount     = 0;
        $this->pendingCount     = 0;
        $this->featureStartTime = microtime(true);
    }

    /**
     * Listens to "feature.after" event.
     *
     * @param FeatureEvent $event
     *
     * @uses printTestSuiteFooter()
     * @uses flushOutputConsole()
     */
    public function afterFeature(FeatureEvent $event)
    {
        $this->printTestSuiteFooter($event->getFeature(), microtime(true) - $this->featureStartTime);
        $this->flushOutputConsole();
    }

    /**
     * Listens to "scenario.before" event.
     *
     * @param ScenarioEvent $event
     */
    public function beforeScenario(ScenarioEvent $event)
    {
        $this->scenarioStartTime = microtime(true);
        $this->scenarioStepsCount = 0;
    }

    /**
     * Listens to "scenario.after" event.
     *
     * @param ScenarioEvent $event
     *
     * @uses printTestCase()
     */
    public function afterScenario(ScenarioEvent $event)
    {
        $this->printTestCase($event->getScenario(), microtime(true) - $this->scenarioStartTime, $event);

        ++$this->scenariosCount;
    }

    /**
     * Listens to "outline.example.before" event.
     *
     * @param OutlineExampleEvent $event
     */
    public function beforeOutlineExample(OutlineExampleEvent $event)
    {
        $this->scenarioStartTime = microtime(true);
    }

    /**
     * Listens to "outline.example.after" event.
     *
     * @param OutlineExampleEvent $event
     *
     * @uses printTestCase()
     */
    public function afterOutlineExample(OutlineExampleEvent $event)
    {
        $this->printTestCase($event->getOutline(), microtime(true) - $this->scenarioStartTime, $event);
    }

    /**
     * Listens to "step.after" event.
     *
     * @param StepEvent $event
     */
    public function afterStep(StepEvent $event)
    {
        if ($event->hasException()) {
            if ($event->getResult() === StepEvent::SKIPPED
             || $event->getResult() === StepEvent::PENDING
             || $event->getResult() === StepEvent::UNDEFINED) {
                $this->pendingCount++;
            } else {
                $this->exceptions[] = $event->getException();
                $this->exceptionsCount++;
                $this->failureCount++;
            }
        }

        ++$this->stepsCount;
        ++$this->scenarioStepsCount;
    }

    /**
     * Prints testsuite header.
     *
     * @param FeatureNode $feature
     */
    protected function printTestSuiteHeader(FeatureNode $feature)
    {
        $this->writeln('<?xml version="1.0" encoding="UTF-8"?>');
    }

    /**
     * Prints testsuite footer.
     *
     * @param FeatureNode $feature
     * @param float       $time
     */
    protected function printTestSuiteFooter(FeatureNode $feature, $time)
    {
        $suiteStats = sprintf('errors="0" failures="%d" skipped="%d" name="%s" tests="%d" time="%F"',
            $this->exceptionsCount,
            $this->pendingCount,
            htmlspecialchars($feature->getTitle()),
            $this->scenariosCount,
            $time
        );

        $this->writeln("<testsuite $suiteStats>");
        $this->writeln(implode("\n", $this->testcases));
        $this->writeln('</testsuite>');
    }

    /**
     * Prints testcase.
     *
     * @param ScenarioNode   $scenario
     * @param float          $time
     * @param EventInterface $event
     */
    protected function printTestCase(ScenarioNode $scenario, $time, EventInterface $event)
    {
        $className = $scenario->getFeature()->getTitle();
        $name = $scenario->getTitle();
        $name .= $event instanceof OutlineExampleEvent
            ? ', Ex #' . ($event->getIteration() + 1)
            : '';
        $caseStats = sprintf('classname="%s" name="%s" time="%F" assertions="%d"',
            htmlspecialchars($className),
            htmlspecialchars($name),
            $time,
            $this->scenarioStepsCount
        );

        $xml  = "    <testcase $caseStats>\n";

        foreach ($this->exceptions as $exception) {
            $error = $this->exceptionToString($exception);
            $elemType = $this->getElementType($event->getResult());
            $elemAttributes = '';
            if ($elemType !== 'skipped') {
                $elemAttributes = sprintf(
                    'message="%s" type="%s"',
                    htmlspecialchars($error),
                    $this->getResultColorCode($event->getResult())
                );
            }

            $xml .= sprintf(
                '        <%s %s>',
                $elemType,
                $elemAttributes
            );
            $exception = str_replace(array('<![CDATA[', ']]>'), '', (string) $exception);
            $xml .= sprintf(
                "<![CDATA[\n%s\n]]></%s>\n",
                $exception,
                $elemType
            );
        }
        $this->exceptions = array();

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

    /**
     * Transform the Excpetion type into the correct element
     * to fulfil the requirements of the JUnit format xsd
     *
     * @see https://svn.jenkins-ci.org/trunk/hudson/dtkit/dtkit-format/dtkit-junit-model/src/main/resources/com/thalesgroup/dtkit/junit/model/xsd/junit-4.xsd
     * @return string
     **/
    protected function getElementType($result)
    {
        switch ($result) {
            case StepEvent::SKIPPED:    return 'skipped';
            case StepEvent::PENDING:    return 'skipped';
            case StepEvent::UNDEFINED:  return 'error';
            case StepEvent::FAILED:     return 'failure';
        }
    }

}
