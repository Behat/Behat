<?php

namespace Behat\Behat\Output\Node\Printer\Helper;

use Behat\Behat\Output\Statistics\Statistics;
use Behat\Behat\Tester\Result\StepResult;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\ScenarioLikeInterface as Scenario;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Output\Printer\OutputPrinter;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * A helper to facilitate rendering required DOMs on the HTML report
 */
class HtmlPrinter
{

    /**
     * @var OutputPrinter
     */
    private $printer;

    public function setOutputPrinter(OutputPrinter $printer)
    {
        $this->printer = $printer;
    }

    public function printOpenTag($tagName, $classes, $content = null)
    {

    }

    public function printHtmlHeader()
    {
        $this->getPrinter()->writeln(
            $this->getTemplate('header')
        );
    }

    public function printHtmlFooter()
    {
        $this->getPrinter()->writeln(
            $this->getTemplate('footer')
        );
    }


    public function openFeature(FeatureNode $feature)
    {
        $tags = $this->renderTags($feature);

        $this->getPrinter()->writeln(
            $this->getTemplate(
                'feature-header',
                array(
                    '{title}' => htmlspecialchars($feature->getTitle()),
                    '{description}' => htmlspecialchars($feature->getDescription()),
                    '{tags}' => $tags
                )
            )
        );
    }

    public function closeFeature(TestResult $result)
    {
        $this->getPrinter()->writeln(
            $this->getTemplate('feature-footer')
        );
    }


    public function openScenario(Scenario $scenario)
    {
        $tags = $this->renderTags($scenario);

        $this->getPrinter()->writeln(
            $this->getTemplate(
                'scenario-header',
                array(
                    '{title}' => htmlspecialchars($scenario->getTitle()),
                    '{keyword}' => htmlspecialchars($scenario->getKeyword()),
                    '{tags}' => $tags
                )
            )
        );
    }

    public function closeScenario(TestResult $result)
    {
        $this->getPrinter()->writeln(
            $this->getTemplate('scenario-footer')
        );
    }

    public function openStep(StepNode $step, TestResult $result, $error = '')
    {

        $vars = array(
            '{keyword}' => $step->getKeyword(),
            '{text}' => htmlspecialchars($step->getText()),
            '{args}' => $this->renderStepArguments($step),
            '{status}' => $this->resultToStatus($result->getResultCode()),
            '{style}' => $this->resultToStyle($result->getResultCode()),
        );

        if (empty($error)) {
            $vars['{err}'] = '';
        } else {
            $vars['{err}'] = $this->getTemplate('error', array('{error}' => htmlspecialchars($error)));
        }

        $this->getPrinter()->writeln($this->getTemplate('step', $vars));
    }

    public function addNavigator(Statistics $statistics)
    {

        $this->getPrinter()->writeln(
            $this->getTemplate(
                'navigator',
                $this->getStats($statistics)
            )
        );

    }

    public function addException($exception)
    {

        $exception = array(
            '<div class="alert alert-danger" role="alert">',
            '   <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>',
            '   <span class="sr-only">Error:</span>',
            "   $exception",
            '</div>',
        );

        $this->getPrinter()->write($exception);
    }

    public function closeTag($tag, $comment = null)
    {
        $closingTag = "</$tag>";

        if (!is_null($comment)) {
            $closingTag .= "<!-- $comment -->";
        }
        $this->getPrinter()->writeln($closingTag);
        return $this;
    }

    /**
     * @return OutputPrinter
     */
    private function getPrinter()
    {
        return $this->printer;
    }

    /**
     * @param string | array $tags
     */
    public function addTags($tags)
    {
        if (is_array($tags)) {
            foreach ($tags as $tag) {
                $this->openButton($tag, 'xs', 'info', array('tags'));
            }
        }
    }

    /**
     * @param string $text
     * @param string $size '' | 'xs' | 'sm' | 'lg' |
     * @param string $style 'default' | 'primary' | 'success' | 'info' | 'warning' | 'danger'
     * @param array $classes list off additional classes
     */
    public function openButton($text, $size = '', $style = 'default', $classes = array())
    {

        if (count($classes) > 0) {
            $classes = ' ' . implode(' ', $classes);
        }

        $button = array(
            '<button type="button" class="btn btn-' . $size . ' btn-' . $style . $classes . '">',
            $text
        );

        $this->getPrinter()->writeln($button);
        $this->closeTag('button');
    }

    private function renderStepArguments(StepNode $step)
    {
        if (!$step->hasArguments()) {
            return '';
        }

        $args = '';

        /** @var \Behat\Gherkin\Node\PyStringNode $arg */
        foreach ($step->getArguments() as $arg) {

            if ($arg instanceof PyStringNode) {

                foreach ($arg->getStrings() as $string) {
                    $args .= $string . PHP_EOL;
                }

            } else {
                $args .= 'new type ';
            }

        }

        return $this->getTemplate(
            'stepArgument',
            array(
                '{arguments}' => htmlspecialchars($args)
            )
        );

    }

    /**
     * @param FeatureNode| Scenario $object
     * @return string
     */
    private function renderTags($object)
    {
        $tags = '';

        if (method_exists($object,'getTags')) {
            foreach ($object->getTags() as $tag) {
                $tags .= $this->getTemplate('tag', array('{tag}' => htmlspecialchars($tag)));
            }
        }

        return $tags;
    }

    private function getTemplate($name, $variables = array())
    {

        $template = file_get_contents(__DIR__ . '/../Html/Templates/' . $name . '.html');

        if (count($variables) > 0) {
            $keys = array_keys($variables);
            $template = str_replace($keys, $variables, $template);
        }

        return $template;
    }

    private function resultToStyle($resultCode)
    {
        switch ($resultCode) {
            case TestResult::SKIPPED:
                return 'info';
            case TestResult::PENDING:
                return 'default';
            case TestResult::FAILED:
                return 'danger';
            case StepResult::UNDEFINED:
                return 'warning';
        }

        return 'success';
    }

    private function resultToStatus($resultCode)
    {
        switch ($resultCode) {
            case TestResult::SKIPPED:
                return 'Skipped';
            case TestResult::PENDING:
                return 'Pending';
            case TestResult::FAILED:
                return 'Failed';
            case StepResult::UNDEFINED:
                return 'Undefined';
        }

        return 'Passed';
    }

    private function getStats(Statistics $statistics)
    {
        $stats = array();
        $mark = '{scenario-';
        foreach($statistics->getScenarioStatCounts() as $code => $count){
            $stats[$mark . $this->resultToStatus($code) . '}'] = $count;
        }
        $stats[$mark . 'Sum}'] = array_sum($statistics->getScenarioStatCounts());
        $mark = '{step-';
        foreach($statistics->getStepStatCounts() as $code => $count){
            $stats[$mark . $this->resultToStatus($code). '}'] = $count;
        }
        $stats[$mark . 'Sum}'] = array_sum($statistics->getStepStatCounts());

        $stats['{timer}'] = $statistics->getTimer();
        $stats['{memory}'] = $statistics->getMemory();

        return $stats;
    }

}
