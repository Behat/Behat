<?php

namespace Behat\Behat\Formatter;

use Behat\Behat\Definition\Definition,
    Behat\Behat\DataCollector\LoggerDataCollector;

use Behat\Gherkin\Node\AbstractNode,
    Behat\Gherkin\Node\FeatureNode,
    Behat\Gherkin\Node\BackgroundNode,
    Behat\Gherkin\Node\AbstractScenarioNode,
    Behat\Gherkin\Node\OutlineNode,
    Behat\Gherkin\Node\ScenarioNode,
    Behat\Gherkin\Node\StepNode,
    Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * HTML formatter.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class HtmlFormatter extends PrettyFormatter
{
    /**
     * Deffered footer template part.
     *
     * @var     string
     */
    protected $footer;

    /**
     * {@inheritdoc}
     */
    protected function getDefaultParameters()
    {
        return array(
            'template_path' => null
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function printSuiteHeader(LoggerDataCollector $logger)
    {
        $this->parameters->set('decorated', false);

        $template = $this->getHtmlTemplate();
        $header         = mb_substr($template, 0, mb_strpos($template, '{{content}}'));
        $this->footer   = mb_substr($template, mb_strpos($template, '{{content}}') + 11);

        $this->writeln($header);
    }

    /**
     * {@inheritdoc}
     */
    protected function printSuiteFooter(LoggerDataCollector $logger)
    {
        $this->printSummary($logger);
        $this->writeln($this->footer);
    }

    /**
     * {@inheritdoc}
     */
    protected function printFeatureHeader(FeatureNode $feature)
    {
        $this->writeln('<div class="feature">');

        parent::printFeatureHeader($feature);
    }

    /**
     * {@inheritdoc}
     */
    protected function printFeatureOrScenarioTags(AbstractNode $node)
    {
        if (count($tags = $node->getTags())) {
            $this->writeln('<ul class="tags">');
            foreach ($tags as $tag) {
                $this->writeln("<li>@$tag</li>");
            }
            $this->writeln('</ul>');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function printFeatureName(FeatureNode $feature)
    {
        $this->writeln('<h2>');
        $this->writeln('<span class="keyword">' . $feature->getKeyword() . ': </span>');
        $this->writeln('<span class="title">' . $feature->getTitle() . '</span>');
        $this->writeln('</h2>');
    }

    /**
     * {@inheritdoc}
     */
    protected function printFeatureDescription(FeatureNode $feature)
    {
        $lines = explode("\n", $feature->getDescription());

        $this->writeln('<p>');
        foreach ($lines as $line) {
            $this->writeln(htmlspecialchars($line) . "<br />");
        }
        $this->writeln('</p>');
    }

    /**
     * {@inheritdoc}
     */
    protected function printFeatureFooter(FeatureNode $feature)
    {
        $this->writeln('</div>');
    }

    /**
     * {@inheritdoc}
     */
    protected function printBackgroundHeader(BackgroundNode $background)
    {
        $this->writeln('<div class="scenario background">');

        $this->printScenarioName($background);
    }

    /**
     * {@inheritdoc}
     */
    protected function printBackgroundFooter(BackgroundNode $background)
    {
        $this->writeln('</ol>');
        $this->writeln('</div>');
    }

    /**
     * {@inheritdoc}
     */
    protected function printScenarioHeader(ScenarioNode $scenario)
    {
        $this->writeln('<div class="scenario">');

        $this->printFeatureOrScenarioTags($scenario);
        $this->printScenarioName($scenario);
    }

    /**
     * {@inheritdoc}
     */
    protected function printScenarioName(AbstractScenarioNode $scenario)
    {
        $this->writeln('<h3>');
        $this->writeln('<span class="keyword">' . $scenario->getKeyword() . ': </span>');
        if (!$scenario instanceof BackgroundNode) {
            $this->writeln('<span class="title">' . $scenario->getTitle() . '</span>');
        }
        $this->printScenarioPath($scenario);
        $this->writeln('</h3>');

        $this->writeln('<ol>');
    }

    /**
     * {@inheritdoc}
     */
    protected function printScenarioFooter(ScenarioNode $scenario)
    {
        $this->writeln('</ol>');
        $this->writeln('</div>');
    }

    /**
     * {@inheritdoc}
     */
    protected function printOutlineHeader(OutlineNode $outline)
    {
        $this->writeln('<div class="scenario outline">');

        $this->printFeatureOrScenarioTags($outline);
        $this->printScenarioName($outline);
    }

    /**
     * {@inheritdoc}
     */
    protected function printOutlineSteps(OutlineNode $outline)
    {
        parent::printOutlineSteps($outline);
        $this->writeln('</ol>');
    }

    /**
     * {@inheritdoc}
     */
    protected function printOutlineExamplesSectionHeader(TableNode $examples)
    {
        $this->writeln('<div class="examples">');
        $this->writeln('<h4>' . $examples->getKeyword() . '</h4>');

        $this->writeln('<table>');
        $this->writeln('<thead>');
        $this->printColorizedTableRow($examples->getRow(0), 'skipped');
        $this->writeln('</thead>');

        $this->writeln('<tbody>');
    }

    /**
     * {@inheritdoc}
     */
    protected function printOutlineExampleResult(TableNode $examples, $iteration, $result, $isSkipped)
    {
        $color  = $this->getResultColorCode($result);

        $this->printColorizedTableRow($examples->getRow($iteration + 1), $color);
        $this->printOutlineExampleResultExceptions($examples, $this->delayedStepEvents);
    }

    /**
     * {@inheritdoc}
     */
    protected function printOutlineExampleResultExceptions(TableNode $examples, array $events)
    {
        $colCount = count($examples->getRow(0));

        foreach ($events as $event) {
            if (!($exception = $event->getException()))
                continue;

            $error = $this->relativizePathsInString($exception->getMessage());

            $this->writeln('<tr class="failed exception">');
            $this->writeln('<td colspan="' . $colCount . '">');
            $this->writeln('<pre class="backtrace">' . htmlspecialchars($error) . '</pre>');
            $this->writeln('</td>');
            $this->writeln('</tr>');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function printOutlineFooter(OutlineNode $outline)
    {
        $this->writeln('</tbody>');
        $this->writeln('</table>');
        $this->writeln('</div>');
        $this->writeln('</div>');
    }

    /**
     * {@inheritdoc}
     */
    protected function printStep(StepNode $step, $result, Definition $definition = null,
                                 $snippet = null, \Exception $exception = null)
    {
        $this->writeln('<li class="' . $this->getResultColorCode($result) . '">');

        parent::printStep($step, $result, $definition, $snippet, $exception);

        $this->writeln('</li>');
    }

    /**
     * {@inheritdoc}
     */
    protected function printStepBlock(StepNode $step, Definition $definition = null, $color)
    {
        $this->writeln('<div class="step">');

        $this->printStepName($step, $definition, $color);
        if (null !== $definition) {
            $this->printStepDefinitionPath($step, $definition);
        }

        $this->writeln('</div>');
    }

    /**
     * {@inheritdoc}
     */
    protected function printStepName(StepNode $step, Definition $definition = null, $color)
    {
        $type   = $step->getType();
        $text   = $this->inOutlineSteps ? $step->getCleanText() : $step->getText();

        if (null !== $definition) {
            $text = $this->colorizeDefinitionArguments($text, $definition, $color);
        }

        $this->writeln('<span class="keyword">' . $type . ' </span>');
        $this->writeln('<span class="text">' . $text . '</span>');
    }

    /**
     * {@inheritdoc}
     */
    protected function printStepDefinitionPath(StepNode $step, Definition $definition)
    {
        $this->printPathComment($definition->getFile(), $definition->getLine());
    }

    /**
     * {@inheritdoc}
     */
    protected function printStepPyStringArgument(PyStringNode $pystring, $color = null)
    {
        $this->writeln('<pre class="argument">' . htmlspecialchars((string) $pystring) . '</pre>');
    }

    /**
     * {@inheritdoc}
     */
    protected function printStepTableArgument(TableNode $table, $color = null)
    {
        $this->writeln('<table class="argument">');

        $this->writeln('<thead>');
        $headers = $table->getRow(0);
        $this->printColorizedTableRow($headers, 'row');
        $this->writeln('</thead>');

        $this->writeln('<tbody>');
        foreach ($table->getHash() as $row) {
            $this->printColorizedTableRow($row, 'row');
        }
        $this->writeln('</tbody>');

        $this->writeln('</table>');
    }

    /**
     * {@inheritdoc}
     */
    protected function printStepException(\Exception $exception, $color)
    {
        $error = $this->relativizePathsInString($exception->getMessage());

        $this->writeln('<pre class="backtrace">' . htmlspecialchars($error) . '</pre>');
    }

    /**
     * {@inheritdoc}
     */
    protected function printStepSnippet(array $snippet)
    {
        $snippets   = array_values($snippet);
        $snippet    = $snippets[0];

        $this->writeln('<div class="snippet"><pre>' . htmlspecialchars($snippet) . '</pre></div>');
    }

    /**
     * {@inheritdoc}
     */
    protected function colorizeDefinitionArguments($text, Definition $definition, $color)
    {
        $regex      = $definition->getRegex();
        $paramColor = $color . '_param';

        // Find arguments with offsets
        $matches = array();
        preg_match($regex, $text, $matches, PREG_OFFSET_CAPTURE);
        array_shift($matches);

        // Replace arguments with colorized ones
        $shift = 0;
        foreach ($matches as $key => $match) {
            if (!is_numeric($key) || -1 === $match[1] || '<' === $match[0][0]) {
                continue;
            }

            $offset = $match[1] + $shift;
            $value  = $match[0];
            $begin  = substr($text, 0, $offset);
            $end    = substr($text, $offset + strlen($value));
            // Keep track of how many extra characters are added
            $shift += strlen($format = "{+strong class=\"$paramColor\"-}%s{+/strong-}") - 2;

            $text = sprintf('%s' . $format . '%s', $begin, $value, $end);
        }

        // Replace "<", ">" with colorized ones
        $text = preg_replace('/(<[^>]+>)/', "<strong class=\"$paramColor\">\$1</strong>", $text);
        $text = strtr($text, array('{+' => '<', '-}' => '>'));

        return $text;
    }

    /**
     * {@inheritdoc}
     */
    protected function printColorizedTableRow($row, $color)
    {
        $this->writeln('<tr class="' . $color . '">');

        foreach ($row as $column) {
            $this->writeln('<td>' . $column . '</td>');
        }

        $this->writeln('</tr>');
    }

    /**
     * {@inheritdoc}
     */
    protected function printPathComment($file, $line, $indentCount = 0)
    {
        $file = $this->relativizePathsInString($file);

        $this->writeln('<span class="path">' . $file . ':' . $line . '</span>');
    }

    /**
     * {@inheritdoc}
     */
    protected function printSummary(LoggerDataCollector $logger) {
        $results = $logger->getScenariosStatuses();
        $result = $results['failed'] > 0 ? 'failed' : 'passed';
        $this->writeln('<div class="summary '.$result.'">');
        parent::printSummary($logger);
        $this->writeln('</div>');
    }

    /**
     * {@inheritdoc}
     */
    protected function printScenariosSummary(LoggerDataCollector $logger) {
        $this->writeln('<p class="scenarios">');
        parent::printScenariosSummary($logger);
        $this->writeln('</p>');
    }

    /**
     * {@inheritdoc}
     */
    protected function printStepsSummary(LoggerDataCollector $logger) {
        $this->writeln('<p class="steps">');
        parent::printStepsSummary($logger);
        $this->writeln('</p>');
    }

    /**
     * {@inheritdoc}
     */
    protected function printTimeSummary(LoggerDataCollector $logger)
    {
        $this->writeln('<p class="time">');
        parent::printTimeSummary($logger);
        $this->writeln('</p>');
    }

    /**
     * {@inheritdoc}
     */
    protected function printStatusesSummary(array $statusesStatistics) {
        $statuses = array();
        $status_tpl = '<strong class="%s">%s</strong>';
        foreach ($statusesStatistics as $status => $count) {
            if ($count) {
                $transStatus = $this->translateChoice(
                    "[1,Inf] %1% $status", $count, array('%1%' => $count)
                );
                $statuses[] = sprintf($status_tpl, $status, $transStatus);
            }
        }
        if (count($statuses)) {
            $this->writeln(' ('.implode(', ', $statuses).')');
        }
    }

    /**
     * Get HTML template.
     *
     * @return  string
     */
    protected function getHtmlTemplate()
    {
        $templatePath = $this->parameters->get('template_path')
                     ?: $this->parameters->get('support_path') . DIRECTORY_SEPARATOR . 'html.tpl';

        if (file_exists($templatePath)) {
            return file_get_contents($templatePath);
        }

        return <<<HTMLTPL
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns ="http://www.w3.org/1999/xhtml">
<head>
    <meta content="text/html;charset=utf-8"/>
    <title>Behat Test Suite</title>
    <link href="http://fonts.googleapis.com/css?family=Lobster" rel="stylesheet" type="text/css"/>
    <style type="text/css">
        body {
            margin:0px;
            padding:0px;
            position:relative;
        }
        #behat {
            float:left;
            font-family: Georgia, serif;
            font-size:18px;
            line-height:26px;
        }
        #behat .statistics {
            float:left;
            width:100%;
            margin-bottom:15px;
        }
        #behat .statistics:before {
            content:'Behat';
            position:absolute;
            color: #1C4B20 !important;
            text-shadow: white 1px 1px 1px;
            font-size:48px !important;
            font-family: Lobster, Tahoma;
            top:22px;
            left:20px;
        }
        #behat .statistics p {
            text-align:right;
            padding:5px 15px;
            margin:0px;
            border-right:10px solid #000;
        }
        #behat .statistics.failed p {
            border-color:#C20000;
        }
        #behat .statistics.passed p {
            border-color:#3D7700;
        }
        #behat .feature {
            margin:15px;
        }
        #behat h2, #behat h3, #behat h4 {
            margin:0px 0px 5px 0px;
            padding:0px;
            font-family:Georgia;
        }
        #behat h2 .title, #behat h3 .title, #behat h4 .title {
            font-weight:normal;
        }
        #behat .path {
            font-size:10px;
            font-weight:normal;
            font-family: 'Bitstream Vera Sans Mono', 'DejaVu Sans Mono', Monaco, Courier, monospace !important;
            color:#999;
            padding:0px 5px;
            float:right;
        }
        #behat h3 .path {
            margin-right:4%;
        }
        #behat ul.tags {
            font-size:14px;
            font-weight:bold;
            color:#246AC1;
            list-style:none;
            margin:0px;
            padding:0px;
        }
        #behat ul.tags li {
            display:inline;
        }
        #behat ul.tags li:after {
            content:' ';
        }
        #behat ul.tags li:last-child:after {
            content:'';
        }
        #behat .feature > p {
            margin-top:0px;
            margin-left:20px;
        }
        #behat .scenario {
            margin-left:20px;
            margin-bottom:40px;
        }
        #behat .scenario > ol {
            margin:0px;
            list-style:none;
            margin-left:20px;
            padding:0px;
        }
        #behat .scenario > ol:after {
            content:'';
            display:block;
            clear:both;
        }
        #behat .scenario > ol li {
            float:left;
            width:95%;
            padding-left:5px;
            border-left:5px solid;
            margin-bottom:4px;
        }
        #behat .scenario > ol li .argument {
            margin:10px 20px;
            font-size:16px;
        }
        #behat .scenario > ol li table.argument {
            border:1px solid #d2d2d2;
        }
        #behat .scenario > ol li table.argument thead td {
            font-weight: bold;
        }
        #behat .scenario > ol li table.argument td {
            padding:5px 10px;
            background:#f3f3f3;
        }
        #behat .scenario > ol li .keyword {
            font-weight:bold;
        }
        #behat .scenario > ol li .path {
            float:right;
        }
        #behat .scenario .examples {
            margin-top:20px;
            margin-left:40px;
        }
        #behat .scenario .examples table {
            margin-left:20px;
        }
        #behat .scenario .examples table thead td {
            font-weight:bold;
            text-align:center;
        }
        #behat .scenario .examples table td {
            padding:2px 10px;
            font-size:16px;
        }
        #behat .scenario .examples table .failed.exception td {
            border-left:5px solid #000;
            border-color:#C20000 !important;
            padding-left:0px;
        }
        pre {
            font-family:monospace;
        }
        .snippet {
            font-size:14px;
            color:#000;
            margin-left:20px;
        }
        .backtrace {
            font-size:12px;
            color:#C20000;
            overflow:hidden;
            margin-left:20px;
        }
        #behat .passed {
            background:#DBFFB4;
            border-color:#65C400 !important;
            color:#3D7700;
        }
        #behat .failed {
            background:#FFFBD3;
            border-color:#C20000 !important;
            color:#C20000;
        }
        #behat .undefined, #behat .pending {
            border-color:#FAF834 !important;
            background:#FCFB98;
            color:#000;
        }
        #behat .skipped {
            background:lightCyan;
            border-color:cyan !important;
            color:#000;
        }
        #behat .summary {
            margin: 15px;
            padding: 10px;
            border-width: 2px;
            border-style: solid;
        }
    </style>
</head>
<body>
    <div id="behat">
        {{content}}
    </div>
</body>
</html>
HTMLTPL;
    }
}
