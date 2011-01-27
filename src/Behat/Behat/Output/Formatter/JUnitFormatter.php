<?php

namespace Behat\Behat\Output\Formatter;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\DependencyInjection\Container;

use Behat\Behat\Tester\StepTester;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * JUnit XML Formatter.
 * Implements JUnit XML output formatter.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class JUnitFormatter implements FormatterInterface, ContainerAwareFormatterInterface
{
    protected $supportPath;
    protected $container;

    protected $xml = '';
    protected $statuses;

    protected $scenarioExceptions = array();

    public function __construct()
    {
        $this->statuses = array(
            StepTester::PASSED          => 'passed'
          , StepTester::SKIPPED         => 'skipped'
          , StepTester::PENDING         => 'pending'
          , StepTester::UNDEFINED       => 'undefined'
          , StepTester::FAILED          => 'failed'
        );
    }

    /**
     * @see     FormatterInterface 
     */
    public function setSupportPath($path)
    {
        $this->supportPath = $path;
    }

    /**
     * @see     ContainerAwareFormatterInterface 
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @see     FormatterInterface
     */
    public function registerListeners(EventDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;

        $dispatcher->connect('step.run.after',          array($this, 'handleStep'),         10);
        $dispatcher->connect('outline.sub.run.after',   array($this, 'printScenario'),      10);
        $dispatcher->connect('scenario.run.after',      array($this, 'printScenario'),      10);
        $dispatcher->connect('feature.run.after',       array($this, 'flushFeatureXML'),    10);
    }

    /**
     * Listen to `feature.run.before` event & init default values. 
     * 
     * @param   Event   $event  notified event
     */
    public function prepareToFeature(Event $event)
    {
        $this->xml              = '';
        $this->testsCount       = 0;
        $this->failuresCount    = 0;
        $this->totalTime        = 0;
    }

    /**
      * Listen to `scenario.run.after` event & generate scenario XML.
      *
      * @param   Event   $event  notified event
      */
    public function printScenario(Event $event)
    {
        $scenario = $event->getSubject();

        $className  = $scenario->getFeature()->getTitle() . '.' . $scenario->getTitle();
        $name       = $scenario->getTitle();
        $time       = $this->container->get('behat.statistics_collector')->getLastScenarioTime();

        $this->testsCount       += 1;
        $this->failuresCount    += 0 < $event->get('result') ? 1 : 0;
        $this->totalTime        += $time;

        $this->xml .= sprintf('<testcase clasname="%s" name="%s" time="%f">' . "\n", $className, $name, $time);
        foreach ($this->scenarioExceptions as $exception) {
            $this->xml .= sprintf(
                '<failure message="%s" type="%s"><![CDATA[%s]]></failure>' . "\n"
              , htmlspecialchars($exception->getMessage())
              , $this->statuses[$event->get('result')]
              , $exception
            );
        }
        $this->xml .= '</testcase>' . "\n";

        $this->scenarioExceptions = array();
    }

    /**
      * Listen to `step.run.after` event & collect step information.
      *
      * @param   Event   $event  notified event
      */
    public function handleStep(Event $event)
    {
        $step = $event->getSubject();

        if (null !== $event->get('exception')) {
            $this->scenarioExceptions[] = $event->get('exception');
        }
    }

    /**
     * Listen to `feature.run.after` event & write feature xml.
     *
     * @param   Event   $event  notified event
     */
    public function flushFeatureXML(Event $event)
    {
        $feature = $event->getSubject();

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= sprintf(
            '<testsuite errors="0" failures="%d" name="%s" tests="%d" time="%f">' . "\n"
          , $this->failuresCount
          , $feature->getTitle()
          , $this->testsCount
          , $this->totalTime
        );
        $xml .= $this->xml;
        $xml .= '</testsuite>';

        $event = new Event($this, 'behat.output.write', array(
            'string' => $xml, 'file' => 'TEST-' . basename($feature->getFile(), '.feature') . '.xml', 'newline' => false
        ));
        $this->dispatcher->notify($event);
    }
}
