<?php

namespace Everzet\Behat\Formatter;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;

use Everzet\Gherkin\Node\ScenarioNode;
use Everzet\Gherkin\Node\BackgroundNode;

use Everzet\Behat\Tester\StepTester;

/*
 * This file is part of the Behat.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Progress Console Formatter.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ProgressFormatter extends ConsoleFormatter implements FormatterInterface
{
    protected $translator;
    protected $container;
    protected $output;
    protected $verbose;
    protected $maxDescriptionLength = 0;

    /**
     * @see     Everzet\Behat\Formatter\FormatterInterface
     */
    public function __construct(Container $container)
    {
        $this->container    = $container;
        $this->output       = $container->getOutputService();
        $this->verbose      = $container->getParameter('formatter.verbose');
    }

    /**
     * @see     Everzet\Behat\Formatter\ConsoleFormatter 
     */
    protected function getTranslator()
    {
        if (!$this->translator) {
            $this->translator = $this->container->getTranslatorService();
            $this->translator->setLocale($this->container->getParameter('formatter.locale'));
        }

        return $this->translator;
    }

    /**
     * @see     Everzet\Behat\Formatter\FormatterInterface
     */
    public function registerListeners(EventDispatcher $dispatcher)
    {
        $dispatcher->connect('step.run.after',          array($this, 'printStep'),          10);

        $dispatcher->connect('suite.run.after',         array($this, 'printEmptyLine'),     10);
        $dispatcher->connect('suite.run.after',         array($this, 'printFailedSteps'),   10);
        $dispatcher->connect('suite.run.after',         array($this, 'printPendingSteps'),  10);
        $dispatcher->connect('suite.run.after',         array($this, 'printStatistics'),    10);
        $dispatcher->connect('suite.run.after',         array($this, 'printSnippets'),      10);
    }

    /**
      * Listen to `step.run.after` event & print step run information.
      *
      * @param   Event   $event  notified event
      */
    public function printStep(Event $event)
    {
        $step = $event->getSubject();

        switch ($event['result']) {
            case StepTester::PASSED:
                $this->write('.', 'passed', false);
                break;
            case StepTester::SKIPPED:
                $this->write('-', 'skipped', false);
                break;
            case StepTester::PENDING:
                $this->write('P', 'pending', false);
                break;
            case StepTester::UNDEFINED:
                $this->write('U', 'undefined', false);
                break;
            case StepTester::FAILED:
                $this->write('F', 'failed', false);
                break;
        }
    }

    /**
      * Listen to `suite.run.after` event & print empty line.
      *
      * @param   Event   $event  notified event
      */
    public function printEmptyLine(Event $event)
    {
        $this->write("\n");
    }

    /**
      * Listen to `suite.run.after` event & print failed steps info.
      *
      * @param   Event   $event  notified event
      */
    public function printFailedSteps(Event $event)
    {
        $statistics = $event->getSubject()->getStatisticsCollectorService();

        if (count($statistics->getFailedStepsEvents())) {
            $this->write(sprintf("(::) %s (::)\n", $this->getTranslator()->trans('failed steps')), 'failed');

            foreach ($statistics->getFailedStepsEvents() as $number => $event) {
                // Print step exception
                if (null !== $event['exception']) {
                    if ($this->verbose) {
                        $error = (string) $event['exception'];
                    } else {
                        $error = $event['exception']->getMessage();
                    }
                    $this->write(
                        sprintf("%s. %s"
                          , str_pad((string) ($number + 1), 2, '0', STR_PAD_LEFT)
                          , strtr($error, array("\n" => "\n    "))
                        )
                    , 'failed');
                }

                $this->printStepEventInformation($event, 'failed');
            }
        }
    }

    /**
      * Listen to `suite.run.after` event & print pending steps info.
      *
      * @param   Event   $event  notified event
      */
    public function printPendingSteps(Event $event)
    {
        $statistics = $event->getSubject()->getStatisticsCollectorService();

        if (count($statistics->getPendingStepsEvents())) {
            $this->write(sprintf("(::) %s (::)\n", $this->getTranslator()->trans('pending steps')), 'failed');

            foreach ($statistics->getPendingStepsEvents() as $number => $event) {
                // Print step exception
                if (null !== $event['exception']) {
                    if ($this->verbose) {
                        $error = (string) $event['exception'];
                    } else {
                        $error = $event['exception']->getMessage();
                    }
                    $this->write(
                        sprintf("%s. %s"
                          , str_pad((string) ($number + 1), 2, '0', STR_PAD_LEFT)
                          , strtr($error, array("\n" => "\n    "))
                        )
                    , 'pending');
                }

                $this->printStepEventInformation($event, 'pending');
            }
        }
    }

    /**
     * Print step information (filepath, fileline, exception description).
     *
     * @param   Event   $event  step event
     * @param   string  $type   information type (pending/failed etc.)
     */
    protected function printStepEventInformation(Event $event, $type)
    {
        $step = $event->getSubject();

        // Print step information
        $description = $this->colorize(
            sprintf("    In step `%s %s'.", $step->getType(), $step->getText())
        , $type);
        $this->maxDescriptionLength = $this->maxDescriptionLength > mb_strlen($description)
            ? $this->maxDescriptionLength
            : mb_strlen($description);
        $this->write($description, null, false);
        $this->printLineSourceComment(
            mb_strlen($description)
          , $event['definition']->getFile()
          , $event['definition']->getLine()
        );

        // Print scenario information
        $item = $step->getParent();
        if ($item instanceof BackgroundNode) {
            $description    = $this->colorize('    From scenario background.', $type);
        } elseif ($item instanceof ScenarioNode) {
            $description    = $this->colorize(
                sprintf("    From scenario %s."
                  , $item->getTitle() ? sprintf("`%s'", $item->getTitle()) : '***'
                )
            , $type);
        }
        $this->maxDescriptionLength = $this->maxDescriptionLength > mb_strlen($description)
            ? $this->maxDescriptionLength
            : mb_strlen($description);
        $this->write($description, null, false);
        $this->printLineSourceComment(
            mb_strlen($description)
          , $item->getFile()
          , $item->getLine()
        );
        $this->write();
    }
}
