<?php

namespace Everzet\Behat\Formatter;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;

use Everzet\Gherkin\Node\FeatureNode;
use Everzet\Gherkin\Node\StepNode;
use Everzet\Gherkin\Node\BackgroundNode;
use Everzet\Gherkin\Node\SectionNode;
use Everzet\Gherkin\Node\ScenarioNode;
use Everzet\Gherkin\Node\OutlineNode;
use Everzet\Gherkin\Node\PyStringNode;
use Everzet\Gherkin\Node\TableNode;
use Everzet\Gherkin\Node\ExamplesNode;

use Everzet\Behat\Exception\Pending;

/*
 * This file is part of the Behat.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Pretty Console Formatter.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class PrettyFormatter extends ConsoleFormatter implements FormatterInterface
{
    protected $translator;
    protected $container;
    protected $output;
    protected $verbose;
    protected $backgroundPrinted            = false;
    protected $outlineStepsPrinted          = false;
    protected $maxDescriptionLength         = 0;
    protected $outlineSubresultExceptions   = array();

    /**
     * @see     Everzet\Behat\Formatter\FormatterInterface
     */
    public function __construct(Container $container)
    {
        $this->container    = $container;
        $this->output       = $container->getBehat_OutputService();
        $this->verbose      = $container->getParameter('behat.formatter.verbose');
    }

    /**
     * @see     Everzet\Behat\Formatter\ConsoleFormatter 
     */
    protected function getTranslator()
    {
        if (!$this->translator) {
            $this->translator = $this->container->getGherkin_TranslatorService();
            $this->translator->setLocale($this->container->getParameter('behat.formatter.locale'));
        }

        return $this->translator;
    }

    /**
     * @see     Everzet\Behat\Formatter\FormatterInterface
     */
    public function registerListeners(EventDispatcher $dispatcher)
    {
        $dispatcher->connect('feature.run.before',      array($this, 'printFeatureHeader'),     10);

        $dispatcher->connect('outline.run.before',      array($this, 'printOutlineHeader'),     10);
        $dispatcher->connect('outline.sub.run.after',   array($this, 'printOutlineSubResult'),  10);
        $dispatcher->connect('outline.run.after',       array($this, 'printOutlineFooter'),     10);

        $dispatcher->connect('scenario.run.before',     array($this, 'printScenarioHeader'),    10);
        $dispatcher->connect('scenario.run.after',      array($this, 'printScenarioFooter'),    10);

        $dispatcher->connect('background.run.before',   array($this, 'printBackgroundHeader'),  10);
        $dispatcher->connect('background.run.after',    array($this, 'printBackgroundFooter'),  10);

        $dispatcher->connect('step.run.after',          array($this, 'printStep'),              10);

        $dispatcher->connect('suite.run.after',         array($this, 'printStatistics'),        10);
        $dispatcher->connect('suite.run.after',         array($this, 'printSnippets'),          10);
    }

    /**
     * Listen to `feature.run.before` event & print feature header.
     *
     * @param   Event   $event  notified event
     */
    public function printFeatureHeader(Event $event)
    {
        $feature = $event->getSubject();

        // Print tags if had ones
        if ($feature->hasTags()) {
            $this->write($this->getTagsString($feature), 'tag');
        }

        // Print feature title
        $this->write(
            $this->getTranslator()->trans('Feature', array(), 'messages', $feature->getLocale())
          . ': ' . $feature->getTitle()
        );

        // Print feature description
        foreach ($feature->getDescription() as $description) {
            $this->write('  ' . $description);
        }
        $this->write();

        // Run fake background to test if it runs without errors & print it output
        if ($feature->hasBackground()) {
            $this->container->getBehat_StatisticsCollectorService()->pause();
            $tester = $this->container->getBehat_BackgroundTesterService();
            $tester->setEnvironment($this->container->getBehat_EnvironmentService());
            $feature->getBackground()->accept($tester);
            $this->container->getBehat_StatisticsCollectorService()->resume();
            $this->backgroundPrinted = true;
        }
    }

    /**
      * Listen to `outline.run.before` event & print outline header.
      *
      * @param  Event   $event  notified event
      */
    public function printOutlineHeader(Event $event)
    {
        $outline    = $event->getSubject();
        $examples   = $outline->getExamples()->getTable();

        // Recalc maximum description length (for filepath-like comments)
        $this->recalcMaxDescriptionLength($outline);

        // Print tags if had ones
        if ($outline->hasTags()) {
            $this->write($this->getTagsString($outline), 'tag');
        }

        // Print outline description
        $description = sprintf("  %s:%s",
            $this->getTranslator()->trans('Scenario Outline', array(), 'messages', $outline->getLocale())
          , $outline->getTitle() ? ' ' . $outline->getTitle() : ''
        );
        $this->write($description, null, false);

        // Print element path & line
        $this->printLineSourceComment(
            mb_strlen($description)
          , $outline->getFile()
          , $outline->getLine()
        );

        // Print outline steps
        $environment = $this->container->getBehat_EnvironmentService();
        $this->container->getBehat_StatisticsCollectorService()->pause();
        foreach ($outline->getSteps() as $step) {
            $tester = $this->container->getBehat_StepTesterService();
            $tester->setEnvironment($environment);
            $tester->setTokens(current($examples->getHash()));
            $tester->skip();
            $step->accept($tester);
        }
        $this->container->getBehat_StatisticsCollectorService()->resume();

        $this->outlineStepsPrinted = true;

        // Print outline examples title
        $this->write(sprintf("\n    %s:",
            $this->getTranslator()->trans('Examples', array(), 'messages', $outline->getLocale())
        ));

        // print outline examples header row
        $this->write(
            preg_replace(
                '/|([^|]*)|/'
              , $this->colorize('$1', 'skipped')
              , '      ' . $examples->getKeysAsString()
            )
        );
    }

    /**
     * Listen to `outline.sub.run.after` event & print outline subscenario results.
     *
     * @param   Event   $event  notified event
     */
    public function printOutlineSubResult(Event $event)
    {
        $outline    = $event->getSubject();
        $examples   = $outline->getExamples()->getTable();

        // print current scenario results row
        $this->write(
            preg_replace(
                '/|([^|]*)|/'
              , $this->colorize('$1', $event['result'])
              , '      ' . $examples->getRowAsString($event['iteration'])
            )
        );

        // Print errors
        foreach ($this->outlineSubresultExceptions as $exception) {
            if ($this->verbose) {
                $error = (string) $exception;
            } else {
                $error = $exception->getMessage();
            }
            if ($exception instanceof Pending) {
                $status = 'pending';
            } else {
                $status = 'failed';
            }
            $this->write('        ' . strtr($error, array("\n" => "\n      ")), $status);
        }
        $this->outlineSubresultExceptions = array();
    }

    /**
      * Listen to `outline.run.after` event & print outline footer.
      *
      * @param   Event   $event  notified event
      */
    public function printOutlineFooter(Event $event)
    {
        $this->write();
    }

    /**
      * Listen to `scenario.run.before` event & print scenario header.
      *
      * @param   Event   $event  notified event
      */
    public function printScenarioHeader(Event $event)
    {
        $scenario = $event->getSubject();

        // Recalc maximum description length (for filepath-like comments)
        $this->recalcMaxDescriptionLength($scenario);

        // Print tags if had ones
        if ($scenario->hasTags()) {
            $this->write($this->getTagsString($scenario), 'tag');
        }

        // Print scenario description
        $description = sprintf("  %s:%s",
            $this->getTranslator()->trans('Scenario', array(), 'messages', $scenario->getLocale())
          , $scenario->getTitle() ? ' ' . $scenario->getTitle() : ''
        );
        $this->write($description, null, false);

        // Print element path & line
        $this->printLineSourceComment(
            mb_strlen($description)
          , $scenario->getFile()
          , $scenario->getLine()
        );
    }

    /**
      * Listen to `scenario.run.after` event & print scenario footer.
      *
      * @param   Event   $event  notified event
      */
    public function printScenarioFooter(Event $event)
    {
        $this->write();
    }

    /**
      * Listen to `background.run.before` event & print background header.
      *
      * @param   Event   $event  notified event
      */
    public function printBackgroundHeader(Event $event)
    {
        if (!$this->backgroundPrinted) {
            $background = $event->getSubject();

            // Recalc maximum description length (for filepath-like comments)
            $this->recalcMaxDescriptionLength($background);

            // Print description
            $description = sprintf("  %s:%s",
                $this->getTranslator()->trans('Background', array(), 'messages', $background->getLocale())
              , $background->getTitle() ? ' ' . $background->getTitle() : ''
            );
            $this->write($description, null, false);

            // Print element path & line
            $this->printLineSourceComment(
                mb_strlen($description)
              , $background->getFile()
              , $background->getLine()
            );
        }
    }

    /**
      * Listen to `background.run.after` event & print background footer.
      *
      * @param   Event   $event  notified event
      */
    public function printBackgroundFooter(Event $event)
    {
        if (!$this->backgroundPrinted) {
            $this->write();
        }
    }

    /**
      * Listen to `step.run.after` event & print step run information.
      *
      * @param   Event   $event  notified event
      */
    public function printStep(Event $event)
    {
        $step = $event->getSubject();

        if (!($step->getParent() instanceof BackgroundNode) || !$this->backgroundPrinted) {
            if (!($step->getParent() instanceof OutlineNode) || !$this->outlineStepsPrinted) {
                // Print step description
                $text = $this->outlineStepsPrinted ? $step->getText() : $step->getCleanText();
                $description = sprintf('    %s %s', $step->getType(), $text);
                $this->write($description, $event['result'], false);

                // Print definition path if found one
                if (null !== $event['definition']) {
                    $this->printLineSourceComment(
                        mb_strlen($description)
                      , $event['definition']->getFile()
                      , $event['definition']->getLine()
                    );
                } else {
                    $this->write();
                }

                // print step arguments
                if ($step->hasArguments()) {
                    foreach ($step->getArguments() as $argument) {
                        if ($argument instanceof PyStringNode) {
                            $this->write($this->getPyString($argument, 6), $event['result']);
                        } elseif ($argument instanceof TableNode) {
                            $this->write($this->getTableString($argument, 6), $event['result']);
                        }
                    }
                }

                // Print step exception
                if (null !== $event['exception']) {
                    if ($this->verbose) {
                        $error = (string) $event['exception'];
                    } else {
                        $error = $event['exception']->getMessage();
                    }
                    $this->write(
                        '      ' . strtr($error, array("\n" => "\n      ")), $event['result']
                    );
                }
            } else {
                if (null !== $event['exception']) {
                    $this->outlineSubresultExceptions[] = $event['exception'];
                }
            }
        }
    }

    /**
     * Recalculate max descriptions size for section elements.
     *
     * @param   SectionNode $scenario   element for calculations
     * 
     * @return  integer                 description length
     */
    protected function recalcMaxDescriptionLength(SectionNode $scenario)
    {
        $max    = $this->maxDescriptionLength;
        $type   = '';

        if ($scenario instanceof OutlineNode) {
            $type = $this->getTranslator()->trans('Scenario Outline', array(), 'messages', $scenario->getLocale());
        } else if ($scenario instanceof ScenarioNode) {    
            $type = $this->getTranslator()->trans('Scenario', array(), 'messages', $scenario->getLocale());
        } else if ($scenario instanceof BackgroundNode) {
            $type = $this->getTranslator()->trans('Background', array(), 'messages', $scenario->getLocale());
        }
        $scenarioDescription = $scenario->getTitle() ? $type . ': ' . $scenario->getTitle() : $type;

        if (($tmp = mb_strlen($scenarioDescription) + 2) > $max) {
            $max = $tmp;
        }

        foreach ($scenario->getSteps() as $step) {
            $stepDescription = $step->getType() . ' ' . $step->getCleanText();
            if (($tmp = mb_strlen($stepDescription) + 4) > $max) {
                $max = $tmp;
            }
        }

        $this->maxDescriptionLength = $max;
    }

    /**
     * Return formatted tag string, prepared for console output.
     *
     * @param   SectionNode $section    section instance
     * 
     * @return  string
     */
    protected function getTagsString(SectionNode $section)
    {
        $tags = array();
        foreach ($section->getTags() as $tag) {
            $tags[] = '@' . $tag;
        }

        return implode(' ', $tags);
    }

    /**
     * Return formatted PyString, prepared for console output.
     *
     * @param   PyStringNode    $pystring   PyString
     * @param   integer         $indent     indentation spaces count
     * 
     * @return  string
     */
    protected function getPyString(PyStringNode $pystring, $indent = 6)
    {
        return strtr(
            sprintf("%s\"\"\"\n%s\n\"\"\"", str_repeat(' ', $indent), (string) $pystring),
            array("\n" => "\n" . str_repeat(' ', $indent))
        );
    }

    /**
     * Return formatted Table, prepared for console output.
     *
     * @param   TableNode   $table      Table instance
     * @param   string      $indent     indentation spaces count
     * 
     * @return  string
     */
    protected function getTableString(TableNode $table, $indent = 6)
    {
        return strtr(
            sprintf(str_repeat(' ', $indent).'%s', $table),
            array("\n" => "\n".str_repeat(' ', $indent))
        );
    }
}

