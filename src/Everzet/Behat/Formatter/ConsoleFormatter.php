<?php

namespace Everzet\Behat\Formatter;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;

use Everzet\Behat\Tester\StepTester;

/*
 * This file is part of the Behat.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Abstract Console Formatter.
 * Implements basic console printing operations.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class ConsoleFormatter
{
    protected $statuses;

    public function __construct()
    {
        $this->statuses = array(
            StepTester::PASSED          => 'passed'
          , StepTester::SKIPPED         => 'skipped'
          , StepTester::PENDING         => 'pending'
          , StepTester::UNDEFINED       => 'undefined'
          , StepTester::FAILED          => 'failed'

          , StepTester::PASSED  + 10    => 'passed_param'
          , StepTester::SKIPPED + 10    => 'skipped_param'
          , StepTester::PENDING + 10    => 'pending_param'
          , StepTester::FAILED  + 10    => 'failed_param'
        );
    }

    /**
     * Return Translator Service. 
     * 
     * @return  Symfony\Component\Translation\TranslatorInterface
     */
    abstract protected function getTranslator();

    /**
      * Listen to some event & print suite statistics.
      *
      * @param   Event   $event  notified event
      */
    public function printStatistics(Event $event)
    {
        $statistics = $event->getSubject()->getBehat_StatisticsCollectorService();

        $this->write(
            $this->getTranslator()->transChoice(
                '{0} No scenarios|{1} 1 scenario|]1,Inf] %1% scenarios'
                , $statistics->getScenariosCount()
                , array('%1%' => $statistics->getScenariosCount())
            )
          , null
          , false
        );

        $statuses = array();
        foreach ($statistics->getScenariosStatuses() as $status => $count) {
            if ($count) {
                $transStatus = $this->getTranslator()->transChoice(
                    "[1,Inf] %1% $status"
                  , $count
                  , array('%1%' => $count)
                );
                $statuses[] = $this->colorize($transStatus, $status);
            }
        }
        $this->write(count($statuses) ? ' ' . sprintf('(%s)', implode(', ', $statuses)) : '');

        $this->write(
            $this->getTranslator()->transChoice(
                '{0} No steps|{1} 1 step|]1,Inf] %1% steps'
                , $statistics->getStepsCount()
                , array('%1%' => $statistics->getStepsCount())
            )
          , null
          , false
        );

        $statuses = array();
        foreach ($statistics->getStepsStatuses() as $status => $count) {
            if ($count) {
                $transStatus = $this->getTranslator()->transChoice(
                    "[1,Inf] %1% $status"
                  , $count
                  , array('%1%' => $count)
                );
                $statuses[] = $this->colorize($transStatus, $status);
            }
        }
        $this->write(count($statuses) ? ' ' . sprintf('(%s)', implode(', ', $statuses)) : '');
    }

    /**
      * Listen to some event & print step definition snippets.
      *
      * @param   Event   $event  notified event
      */
    public function printSnippets(Event $event)
    {
        $statistics = $event->getSubject()->getBehat_StatisticsCollectorService();

        if (count($statistics->getDefinitionsSnippets())) {
            $this->write("\n" .
                $this->getTranslator()->trans(
                    "You can implement step definitions for undefined steps with these snippets:"
                ) .
            "\n", 'undefined');

            foreach ($statistics->getDefinitionsSnippets() as $key => $snippet) {
                $this->write($snippet, 'undefined');
                $this->write();
            }
        }
    }

    /**
     * Set color of string.
     *
     * @param   string          $string     string to colorize
     * @param   integer|string  $result     result code or status string
     * 
     * @return  string                      colorized string (with console color codes added)
     */
    protected function colorize($string, $result = null)
    {
        if (null !== $result) {
            return $this->colorizeStart($result) . $string . $this->colorizeFinish();
        } else {
            return $string;
        }
    }

    /**
     * Get color start code. 
     * 
     * @param   integer|string  $result     result code or status string
     *
     * @return  string                      console color code start
     */
    protected function colorizeStart($result)
    {
        return sprintf("\033[%sm", $this->getStatusColorCode(is_int($result) ? $this->statuses[$result] : $result));
    }

    /**
     * Get color end code. 
     * 
     * @return  string                      console color code end
     */
    protected function colorizeFinish()
    {
        return "\033[0m";
    }

    /**
     * Print string to console.
     *
     * @param   string          $string     string to print
     * @param   integer|string  $result     result code or status string to colorize
     * @param   boolean         $newline    add newline after?
     */
    protected function write($string = '', $result = null, $newline = true)
    {
        $this->output->write($this->colorize($string, $result), $newline, 1);
    }

    /**
     * Return result code for custom status.
     *
     * @param   string  $status status (passed/skipped/failed etc.)
     * 
     * @return  integer         result code
     */
    protected function getStatusColorCode($status)
    {
        switch ($status) {
            case 'failed':
                return 31;
            case 'undefined':
            case 'pending':
                return 33;
            case 'skipped':
            case 'tag':
                return 36;
            case 'comment':
                return 30;
            case 'passed':
                return 32;
            case 'failed_param':
                return '31;1';
            case 'pending_param':
                return '33;1';
            case 'skipped_param':
                return '36;1';
            case 'passed_param':
                return '32;1';
            default:
                return 32;
        }
    }

    /**
     * Print comment line with source info.
     *
     * @param   integer $lineLength     current line length
     * @param   string  $file           source file
     * @param   integer $line           source line
     */
    protected function printLineSourceComment($lineLength, $file, $line)
    {
        $indent = $lineLength > $this->maxDescriptionLength
            ? 0
            : $this->maxDescriptionLength - $lineLength;
        $file   = preg_replace('/.*\/features\//', 'features/', $file);

        $this->write(sprintf("%s # %s:%d", str_repeat(' ', $indent), $file, $line), 'comment');
    }
}

