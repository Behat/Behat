<?php

namespace Everzet\Behat\Formatter;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;

use Everzet\Behat\Runner\RunnerInterface;
use Everzet\Behat\Runner\ScenarioRunner;
use Everzet\Behat\Runner\BackgroundRunner;

use Everzet\Behat\RunableNode\RunableNodeInterface;
use Everzet\Behat\RunableNode\StepNode;
use Everzet\Behat\RunableNode\ScenarioNode;
use Everzet\Behat\RunableNode\BackgroundNode;

/*
 * This file is part of the behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Console progress output formatter (phpUnit-like).
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ProgressFormatter extends ConsoleFormatter implements FormatterInterface
{
    protected $container;
    protected $output;
    protected $verbose;
    protected $maxDescriptionLength = 0;

    /**
     * @see Everzet\Behat\Formatter\FormatterInterface
     */
    public function __construct(Container $container)
    {
        $this->container    = $container;
        $this->output       = $container->getOutputService();
        $this->verbose      = $container->getParameter('formatter.verbose');

        $this->output->setStyle('failed',      array('fg' => 'red'));
        $this->output->setStyle('undefined',   array('fg' => 'yellow'));
        $this->output->setStyle('pending',     array('fg' => 'yellow'));
        $this->output->setStyle('passed',      array('fg' => 'green'));
        $this->output->setStyle('skipped',     array('fg' => 'cyan'));
        $this->output->setStyle('comment',     array('fg' => 'black'));
        $this->output->setStyle('tag',         array('fg' => 'cyan'));
    }

    /**
     * @see Everzet\Behat\Formatter\FormatterInterface
     */
    public function registerListeners(EventDispatcher $dispatcher)
    {
        $this->registerRunCounters($dispatcher);

        $dispatcher->connect('step.run.after',          array($this, 'printStep'),          10);
        $dispatcher->connect('step.skip.after',         array($this, 'printStep'),          10);

        $dispatcher->connect('suite.run.after',         array($this, 'printEmptyLine'),     10);
        $dispatcher->connect('suite.run.after',         array($this, 'printFailedSteps'),   10);
        $dispatcher->connect('suite.run.after',         array($this, 'printPendingSteps'),  10);
        $dispatcher->connect('suite.run.after',         array($this, 'printStatistics'),    10);
        $dispatcher->connect('suite.run.after',         array($this, 'printSnippets'),      10);
    }

    /**
      * Listens to `step.post_test` event & prints step runner information
      *
      * @param   Event   $event  notified event
      */
    public function printStep(Event $event)
    {
        $step = $event->getSubject();

        switch ($step->getResult()) {
            case RunableNodeInterface::PASSED:
                $this->write('.', 'passed', false);
                break;
            case RunableNodeInterface::SKIPPED:
                $this->write('-', 'skipped', false);
                break;
            case RunableNodeInterface::PENDING:
                $this->write('P', 'pending', false);
                break;
            case RunableNodeInterface::UNDEFINED:
                $this->write('U', 'undefined', false);
                break;
            case RunableNodeInterface::FAILED:
                $this->write('F', 'failed', false);
                break;
        }
    }

    /**
      * Listens to `suite.post_test` event & prints empty line
      *
      * @param   Event   $event  notified event
      */
    public function printEmptyLine(Event $event)
    {
        $this->write("\n");
    }

    /**
      * Listens to `suite.post_test` event & prints failed steps info
      *
      * @param   Event   $event  notified event
      */
    public function printFailedSteps(Event $event)
    {
        if (count($this->failedSteps)) {
            $this->write("(::) failed steps (::)\n", 'failed');

            foreach ($this->failedSteps as $number => $step) {
                // Print step exception
                if (null !== $step->getException()) {
                    if ($this->verbose) {
                        $error = (string) $step->getException();
                    } else {
                        $error = $step->getException()->getMessage();
                    }
                    $this->write(
                        sprintf("%s. %s"
                          , str_pad((string) ($number + 1), 2, '0', STR_PAD_LEFT)
                          , strtr($error, array("\n" => "\n    "))
                        )
                    , 'failed');
                }

                $this->printStepInformation($step, 'failed');
            }
        }
    }

    /**
      * Listens to `suite.post_test` event & prints pending steps info
      *
      * @param   Event   $event  notified event
      */
    public function printPendingSteps(Event $event)
    {
        if (count($this->pendingSteps)) {
            $this->write("(::) pending steps (::)\n", 'pending');

            foreach ($this->pendingSteps as $number => $step) {
                // Print step exception
                if (null !== $step->getException()) {
                    if ($this->verbose) {
                        $error = (string) $step->getException();
                    } else {
                        $error = $step->getException()->getMessage();
                    }
                    $this->write(
                        sprintf("%s. %s"
                          , str_pad((string) ($number + 1), 2, '0', STR_PAD_LEFT)
                          , strtr($error, array("\n" => "\n    "))
                        )
                    , 'pending');
                }

                $this->printStepInformation($step, 'pending');
            }
        }
    }

    /**
     * Print step information (filepath, fileline, exception description)
     *
     * @param   RunnerInterface $stepRunner runner instance
     * @param   string          $type       information type (pending/failed etc.)
     */
    protected function printStepInformation(StepNode $step, $type)
    {
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
          , $step->getDefinition()->getFile()
          , $step->getDefinition()->getLine()
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
