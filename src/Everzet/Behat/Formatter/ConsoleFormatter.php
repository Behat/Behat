<?php

namespace Everzet\Behat\Formatter;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;

use Everzet\Behat\Tester\StepTester;

abstract class ConsoleFormatter
{
    protected $statuses = array(
        StepTester::PASSED    => 'passed'
      , StepTester::SKIPPED   => 'skipped'
      , StepTester::PENDING   => 'pending'
      , StepTester::UNDEFINED => 'undefined'
      , StepTester::FAILED    => 'failed'
    );

    /**
      * Listens to `suite.post_test` event & prints all tests statistics
      *
      * @param   Event   $event  notified event
      */
    public function printStatistics(Event $event)
    {
        $statistics = $event->getSubject()->getStatisticsCollectorService();

        $statuses = array();
        foreach ($statistics->getScenariosStatuses() as $status => $count) {
            if ($count) {
                $statuses[] = $this->colorize(sprintf('%s %s', $count, $status), $status);
            }
        }
        $this->write(sprintf('%d scenarios %s',
            $statistics->getScenariosCount()
          , count($statuses) ? sprintf('(%s)', implode(', ', $statuses)) : ''
        ));

        $statuses = array();
        foreach ($statistics->getStepsStatuses() as $status => $count) {
            if ($count) {
                $statuses[] = $this->colorize(sprintf('%s %s', $count, $status), $status);
            }
        }
        $this->write(sprintf('%d steps %s',
            $statistics->getStepsCount()
          , count($statuses) ? sprintf('(%s)', implode(', ', $statuses)) : ''
        ));
    }

    /**
      * Listens to `suite.post_test` event & prints step snippets for undefined steps
      *
      * @param   Event   $event  notified event
      */
    public function printSnippets(Event $event)
    {
        $statistics = $event->getSubject()->getStatisticsCollectorService();

        if (count($statistics->getDefinitionsSnippets())) {
            $this->write("\n" .
                "You can implement step definitions for undefined steps with these snippets:" .
            "\n", 'undefined');

            foreach ($statistics->getDefinitionsSnippets() as $key => $snippet) {
                $this->write($snippet, 'undefined');
                $this->write();
            }
        }
    }

    protected function colorize($string, $result = null)
    {
        if (null !== $result) {
            return sprintf("\033[%sm%s\033[0m",
                $this->getStatusColorCode(is_int($result) ? $this->statuses[$result] : $result)
              , $string
              , $this->getStatusColorCode(is_int($result) ? $this->statuses[$result] : $result)
            );
        } else {
            return $string;
        }
    }

    protected function write($string = '', $result = null, $newline = true)
    {
        $this->output->write($this->colorize($string, $result), $newline, 1);
    }

    /**
     * Returns color code for custom status
     *
     * @param   string  $status status (passed/skipped/failed etc.)
     * 
     * @return  integer         console color code
     */
    protected function getStatusColorCode($status)
    {
        $colorCode = 32;
        switch ($status) {
            case 'failed':
                $colorCode = 31;
                break;
            case 'undefined':
            case 'pending':
                $colorCode = 33;
                break;
            case 'skipped':
            case 'tag':
                $colorCode = 36;
                break;
            case 'comment':
                $colorCode = 30;
                break;
        }

        return $colorCode;
    }

    /**
     * Prints comment line with source info
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
