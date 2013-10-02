<?php

namespace Behat\Behat\Output\Formatter;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Definition\DefinitionInterface;
use Behat\Behat\Event\BackgroundEvent;
use Behat\Behat\Event\EventInterface;
use Behat\Behat\Event\ExampleEvent;
use Behat\Behat\Event\FeatureEvent;
use Behat\Behat\Event\OutlineEvent;
use Behat\Behat\Event\ScenarioEvent;
use Behat\Behat\Event\StepEvent;
use Behat\Behat\Exception\UndefinedException;
use Behat\Behat\RunControl\UseCase\CollectStatistics;
use Behat\Gherkin\Node\BackgroundNode;
use Behat\Gherkin\Node\ExampleNode;
use Behat\Gherkin\Node\NodeInterface;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

/**
 * Progress formatter.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class HtmlFormatter extends CliFormatter
{
    /**
     * @var StepEvent[]
     */
    private $exampleRowEvents;

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            EventInterface::BEFORE_EXERCISE   => array('beginDocument', -50),
            EventInterface::BEFORE_FEATURE    => array('beginFeature', -50),
            EventInterface::BEFORE_BACKGROUND => array('beginBackground', -50),
            EventInterface::AFTER_BACKGROUND  => array('endBackground', -50),
            EventInterface::BEFORE_SCENARIO   => array('beginScenario', -50),
            EventInterface::AFTER_SCENARIO    => array('endScenario', -50),
            EventInterface::BEFORE_OUTLINE    => array('beginOutline', -50),
            EventInterface::AFTER_OUTLINE     => array('endOutline', -50),
            EventInterface::AFTER_STEP        => array('printStep', -50),
            EventInterface::AFTER_EXAMPLE     => array('printExampleRow', -50),
            EventInterface::AFTER_FEATURE     => array('endFeature', -50),
            EventInterface::AFTER_EXERCISE    => array('endDocument', -50),
        );
    }

    /**
     * Returns formatter name.
     *
     * @return string
     */
    public function getName()
    {
        return 'html';
    }

    /**
     * Returns formatter description.
     *
     * @return string
     */
    public function getDescription()
    {
        return 'Generates a nice looking HTML report.';
    }

    /**
     * Begins HTML document.
     */
    public function beginDocument()
    {
        $template = $this->getHtmlTemplate();
        $header = mb_substr($template, 0, mb_strpos($template, '{{content}}'));

        $this->writeln($header);
    }

    /**
     * Prints feature header and opens feature div.
     *
     * @param FeatureEvent $event
     */
    public function beginFeature(FeatureEvent $event)
    {
        $feature = $event->getFeature();

        $this->writeln('<div class="feature">');

        if ($feature->hasTags()) {
            $this->writeln('<ul class="tags">');
            foreach ($feature->getTags() as $tag) {
                $this->writeln(sprintf('<li>@%s</li>', htmlspecialchars($tag)));
            }
            $this->writeln('</ul>');
        }

        $this->writeln('<h2>');
        $this->writeln(sprintf('<span class="keyword">%s:</span>', $feature->getKeyword()));
        $this->writeln(sprintf('<span class="title">%s</span>', htmlspecialchars($feature->getTitle())));
        $this->writeln('</h2>');

        if ($feature->hasDescription()) {
            $this->writeln('<p>');
            foreach (explode("\n", $feature->getDescription()) as $line) {
                $this->writeln(htmlspecialchars($line) . '<br />');
            }
            $this->writeln('</p>');
        }
    }

    /**
     * Prints background header and opens background div.
     *
     * @param BackgroundEvent $event
     */
    public function beginBackground(BackgroundEvent $event)
    {
        $scenario = $event->getScenario();
        $container = $event->getContainer();
        $background = $event->getBackground();

        if ($container instanceof ExampleNode && 0 !== $container->getIndex()) {
            return;
        }

        if (0 !== $scenario->getIndex()) {
            return;
        }

        $this->writeln('<div class="scenario background">');

        $this->writeln('<h3>');
        $this->writeln(sprintf('<span class="keyword">%s:</span>', $background->getKeyword()));
        $this->writeln(sprintf('<span class="title">%s</span>', htmlspecialchars($background->getTitle())));

        if ($this->getParameter('paths')) {
            $this->writeln(sprintf('<span class="path">%s</span>', $this->getNodePath($background)));
        }

        $this->writeln('</h3>');

        $this->writeln('<ol>');
    }

    /**
     * Closes background div.
     *
     * @param BackgroundEvent $event
     */
    public function endBackground(BackgroundEvent $event)
    {
        $scenario = $event->getScenario();
        $container = $event->getContainer();

        if ($container instanceof ExampleNode && 0 !== $container->getIndex()) {
            return;
        }

        if (0 !== $scenario->getIndex()) {
            return;
        }

        $this->writeln('</ol>');
        $this->writeln('</div>');

        $this->writeln('<div class="scenario">');

        if ($scenario->hasOwnTags()) {
            $this->writeln('<ul class="tags">');
            foreach ($scenario->getOwnTags() as $tag) {
                $this->writeln(sprintf('<li>@%s</li>', htmlspecialchars($tag)));
            }
            $this->writeln('</ul>');
        }

        $this->writeln('<h3>');
        $this->writeln(sprintf('<span class="keyword">%s:</span>', $scenario->getKeyword()));
        $this->writeln(sprintf('<span class="title">%s</span>', htmlspecialchars($scenario->getTitle())));

        if ($this->getParameter('paths')) {
            $this->writeln(sprintf('<span class="path">%s</span>', $this->getNodePath($scenario)));
        }

        $this->writeln('</h3>');

        $this->writeln('<ol>');
    }

    /**
     * Prints scenario header and opens scenario div.
     *
     * @param ScenarioEvent $event
     */
    public function beginScenario(ScenarioEvent $event)
    {
        $scenario = $event->getScenario();
        $feature = $scenario->getFeature();

        if ($feature->hasBackground() && 0 == $scenario->getIndex()) {
            return;
        }

        $this->writeln('<div class="scenario">');

        if ($scenario->hasOwnTags()) {
            $this->writeln('<ul class="tags">');
            foreach ($scenario->getOwnTags() as $tag) {
                $this->writeln(sprintf('<li>@%s</li>', htmlspecialchars($tag)));
            }
            $this->writeln('</ul>');
        }

        $this->writeln('<h3>');
        $this->writeln(sprintf('<span class="keyword">%s:</span>', $scenario->getKeyword()));
        $this->writeln(sprintf('<span class="title">%s</span>', htmlspecialchars($scenario->getTitle())));

        if ($this->getParameter('paths')) {
            $this->writeln(sprintf('<span class="path">%s</span>', $this->getNodePath($scenario)));
        }

        $this->writeln('</h3>');

        $this->writeln('<ol>');
    }

    /**
     * Closes scenario div.
     */
    public function endScenario()
    {
        $this->writeln('</ol>');
        $this->writeln('</div>');
    }

    /**
     * Prints outline header and opens outline div.
     *
     * @param OutlineEvent $event
     */
    public function beginOutline(OutlineEvent $event)
    {
        $outline = $event->getOutline();

        $this->writeln('<div class="scenario outline">');

        if ($outline->hasOwnTags()) {
            $this->writeln('<ul class="tags">');
            foreach ($outline->getOwnTags() as $tag) {
                $this->writeln(sprintf('<li>@%s</li>', htmlspecialchars($tag)));
            }
            $this->writeln('</ul>');
        }

        $this->writeln('<h3>');
        $this->writeln(sprintf('<span class="keyword">%s:</span>', $outline->getKeyword()));
        $this->writeln(sprintf('<span class="title">%s</span>', htmlspecialchars($outline->getTitle())));

        if ($this->getParameter('paths')) {
            $this->writeln(sprintf('<span class="path">%s</span>', $this->getNodePath($outline)));
        }

        $this->writeln('</h3>');

        $this->writeln('<ol>');
    }

    /**
     * Prints example row results.
     *
     * @param ExampleEvent $event
     */
    public function printExampleRow(ExampleEvent $event)
    {
        $example = $event->getExample();
        $table = $example->getOutline()->getExampleTable();

        if (0 == $example->getIndex()) {
            $this->writeln('</ol>');

            $this->writeln('<div class="examples">');
            $this->writeln(sprintf('<h4>%s</h4>', $table->getKeyword()));

            $this->writeln('<table>');
            $this->writeln('<thead>');
            $this->printTableRow($table->getRow(0), 'skipped');
            $this->writeln('</thead>');
        }

        $this->printExampleRowResult($event);

        if (count($example->getOutline()->getExamples()) - 1 == $example->getIndex()) {
            $this->writeln('</table>');
        }

        $this->exampleRowEvents = array();
    }

    /**
     * Closes outline div.
     */
    public function endOutline()
    {
        $this->writeln('</div>');
        $this->writeln('</div>');
    }

    /**
     * Prints step.
     *
     * @param StepEvent $event
     */
    public function printStep(StepEvent $event)
    {
        $step = $event->getStep();

        // It is a background step
        if ($step->getContainer() instanceof BackgroundNode) {
            $this->printBackgroundStep($event);

            return;
        }

        // It is an example step
        if ($step->getContainer() instanceof ExampleNode) {
            $this->printExampleStep($event);

            return;
        }

        $this->printStepBody($event);
    }

    /**
     * Closes feature div.
     */
    public function endFeature()
    {
        $this->writeln('</div>');
    }

    /**
     * Ends HTML document.
     */
    public function endDocument()
    {
        $this->printSummary();

        $template = $this->getHtmlTemplate();
        $footer = mb_substr($template, mb_strpos($template, '{{content}}') + 11);

        $this->writeln($footer);
    }

    protected function printBackgroundStep(StepEvent $event)
    {
        // Skip non-failing background steps in scenarios
        if (0 !== $event->getScenario()->getIndex() && $event->getStatus() < StepEvent::FAILED) {
            return;
        }

        if ($event->getContainer() instanceof ExampleNode && 0 !== $event->getContainer()->getIndex()) {
            return;
        }

        $this->printStepBody($event);
    }

    protected function printExampleStep(StepEvent $event)
    {
        if (0 == $event->getStep()->getContainer()->getIndex()) {
            $this->printStepBody($event, true);
        }

        $this->exampleRowEvents[] = $event;
    }

    protected function printStepBody(StepEvent $event, $outlineStep = false)
    {
        $step = $event->getStep();

        // If it is example step - use outline step instead
        if ($outlineStep) {
            $steps = $step->getContainer()->getOutline()->getSteps();
            $step = $steps[$step->getIndex()];
        }

        $type = $step->getType();
        $text = $step->getText();
        $color = $this->getColorCode($outlineStep ? StepEvent::SKIPPED : $event->getStatus());

        $this->writeln(sprintf('<li class="%s">', $color));
        $this->writeln('<div class="step">');

        if ($event->hasDefinition()) {
            $text = $this->colorizeDefinitionArguments($text, $event->getDefinition(), $color);
        }

        $this->writeln(sprintf('<span class="keyword">%s:</span>', $type));
        $this->writeln(sprintf('<span class="text">%s</span>', $text));

        if ($this->getParameter('paths') && $event->hasDefinition()) {
            $path = $event->getDefinition()->getPath();
            if (false !== strpos($path, '::') && $baseUrl = $this->getParameter('paths_base_url')) {
                list($class, $method) = explode('::', $path);
                $method = new \ReflectionMethod($class, mb_substr($method, 0, -2, 'utf8'));
                $fsPath = $this->relativizePathsInString($method->getFileName());
                $url = sprintf('%s/%s', rtrim($baseUrl, '/'), ltrim(str_replace('\\', '/', $fsPath), '/'));
                $this->writeln(sprintf('<span class="path"><a href="%s:%d">%s</a></span>', $url, $method->getStartLine(), $path));
            } else {
                $this->writeln(sprintf('<span class="path">%s</span>', $path));
            }
        }

        // Print multiline arguments
        if ($this->getParameter('multiline_arguments') && $step->hasArguments()) {
            foreach ($step->getArguments() as $argument) {
                if ($argument instanceof PyStringNode) {
                    $this->printStepPyStringArgument($argument);
                } elseif ($argument instanceof TableNode) {
                    $this->printStepTableArgument($argument);
                }
            }
        }

        if ($outlineStep) {
            return;
        }

        // Print step StdOut
        if ($event->hasStdOut()) {
            $this->writeln(sprintf('<pre class="stdout">%s</pre>', htmlspecialchars($event->getStdOut())));
        }

        // Print step exception
        if ($event->hasException() && !($event->getException() instanceof UndefinedException)) {
            $error = $this->exceptionToString($event->getException());
            $error = $this->relativizePathsInString($error);
            $this->writeln(sprintf('<pre class="backtrace">%s</pre>', htmlspecialchars($error)));
        }

        $this->writeln('</li>');
    }

    protected function printSummary()
    {
        $stats = $this->getStatisticsCollector();

        $color = 'passed';
        if (count($stats->getFailedStepsEvents())) {
            $color = 'failed';
        }

        $this->writeln(sprintf('<div class="summary %s">', $color));

        $this->writeln('<div class="counters">');
        $count = $stats->getScenariosCount();
        $header = $this->translateChoice('scenarios_count', $count, array('%1%' => $count));
        $this->writeln(sprintf('<p class="scenarios">%s', $header));
        $this->printStatusesSummary($stats->getScenariosStatuses());
        $this->writeln('</p>');

        $count = $stats->getStepsCount();
        $header = $this->translateChoice('steps_count', $count, array('%1%' => $count));
        $this->writeln(sprintf('<p class="steps">%s', $header));
        $this->printStatusesSummary($stats->getStepsStatuses());
        $this->writeln('</p>');


        if ($this->getParameter('time')) {
            $this->printTimeSummary($stats);
        }
        $this->writeln('</div>');

        $this->writeln(<<<'HTML'
<div class="switchers">
    <a href="javascript:void(0)" id="behat_show_all">[+] all</a>
    <a href="javascript:void(0)" id="behat_hide_all">[-] all</a>
</div>
HTML
        );

        $this->writeln(sprintf('</div>'));
    }

    protected function printStatusesSummary(array $statusesStatistics)
    {
        $statuses = array();
        $statusTpl = '<strong class="%s">%s</strong>';
        foreach ($statusesStatistics as $status => $count) {
            if ($count) {
                $transStatus = $this->translateChoice(
                    "{$status}_count", $count, array('%1%' => $count)
                );
                $statuses[] = sprintf($statusTpl, $status, $transStatus);
            }
        }

        if (count($statuses)) {
            $this->writeln(' (' . implode(', ', $statuses) . ')');
        }
    }

    protected function printTimeSummary(CollectStatistics $stats)
    {
        $this->writeln('<p class="time">');
        $time = $stats->getTotalTime();
        $minutes = floor($time / 60);
        $seconds = round($time - ($minutes * 60), 3);

        $this->writeln($minutes . 'm' . $seconds . 's');
        $this->writeln('</p>');
    }

    protected function printExampleRowResult(ExampleEvent $event)
    {
        $example = $event->getExample();
        $color = $this->getColorCode($event->getStatus());
        $table = $example->getOutline()->getExampleTable();

        $this->printTableRow($table->getRow($example->getIndex() + 1), $color);

        foreach ($this->exampleRowEvents as $event) {
            if ($event->hasStdOut()) {
                $this->writeln(sprintf('<tr><td colspan="%d">', count($table->getRow($example->getIndex() + 1))));
                $this->writeln(sprintf('<pre class="stdout">%s</pre>', htmlspecialchars($event->getStdOut())));
                $this->writeln('</td></tr>');
            }

            if ($event->hasException() && !($event->getException() instanceof UndefinedException)) {
                $this->writeln(sprintf('<tr><td colspan="%d">', count($table->getRow($example->getIndex() + 1))));
                $error = $this->exceptionToString($event->getException());
                $error = $this->relativizePathsInString($error);
                $this->writeln(sprintf('<pre class="backtrace">%s</pre>', htmlspecialchars($error)));
                $this->writeln('</td></tr>');
            }
        }
    }

    protected function printStepPyStringArgument(PyStringNode $pystring)
    {
        $this->writeln(sprintf('<pre class="argument">%s</pre>', htmlspecialchars($pystring->getRaw())));
    }

    protected function printStepTableArgument(TableNode $table)
    {
        $this->writeln('<table class="argument">');

        $this->writeln('<thead>');
        $headers = $table->getRow(0);
        $this->printTableRow($headers);
        $this->writeln('</thead>');

        $this->writeln('<tbody>');
        foreach ($table->getHash() as $row) {
            $this->printTableRow($row);
        }
        $this->writeln('</tbody>');

        $this->writeln('</table>');
    }

    protected function printTableRow(array $row, $class = 'row')
    {
        $this->writeln(sprintf('<tr class="%s">', $class));

        foreach ($row as $column) {
            $this->writeln(sprintf('<td>%s</td>', htmlspecialchars($column)));
        }

        $this->writeln('</tr>');
    }

    /**
     * {@inheritdoc}
     */
    protected function colorizeDefinitionArguments($text, DefinitionInterface $definition, $color)
    {
        $regex = $definition->getRegex();
        $paramColor = $color . '_param';

        // If it's just a string - skip
        if ('/' !== substr($regex, 0, 1)) {
            return $text;
        }

        // Find arguments with offsets
        $matches = array();
        preg_match($regex, $text, $matches, PREG_OFFSET_CAPTURE);
        array_shift($matches);

        // Replace arguments with colorized ones
        $shift = 0;
        $lastReplacementPosition = 0;
        foreach ($matches as $key => $match) {
            if (!is_numeric($key) || -1 === $match[1] || false !== strpos($match[0], '<')) {
                continue;
            }

            $offset = $match[1] + $shift;
            $value = $match[0];

            // Skip inner matches
            if ($lastReplacementPosition > $offset) {
                continue;
            }
            $lastReplacementPosition = $offset + strlen($value);

            $begin = substr($text, 0, $offset);
            $end = substr($text, $offset + strlen($value));
            $format = "{+strong class=\"$paramColor\"-}%s{+/strong-}";
            $text = sprintf('%s' . $format . '%s', $begin, $value, $end);

            // Keep track of how many extra characters are added
            $shift += strlen($format) - 2;
            $lastReplacementPosition += strlen($format) - 2;
        }

        // Replace "<", ">" with colorized ones
        $text = preg_replace('/(<[^>]+>)/', "{+strong class=\"$paramColor\"-}\$1{+/strong-}", $text);
        $text = htmlspecialchars($text, ENT_NOQUOTES);
        $text = strtr($text, array('{+' => '<', '-}' => '>'));

        return $text;
    }

    protected function getNodePath(NodeInterface $node)
    {
        $path = sprintf('%s:%d', $this->relativizePathsInString($node->getFile()), $node->getLine());

        return $this->getReadablePath($path);
    }

    protected function getReadablePath($path)
    {
        if ($baseUrl = $this->getParameter('paths_base_url')) {
            $url = sprintf('%s/%s', rtrim($baseUrl, '/'), ltrim(str_replace('\\', '/', $path), '/'));

            return sprintf('<a href="%s">%s</a>', $url, $path);
        }

        return $path;
    }

    protected function relativizePathsInString($string)
    {
        if ($basePath = $this->getParameter('base_path')) {
            $basePath = realpath($basePath) . DIRECTORY_SEPARATOR;
            $string = str_replace($basePath, '', $string);
        }

        return $string;
    }

    /**
     * Returns default parameters to construct ParameterBag.
     *
     * @return array
     */
    protected function getDefaultParameters()
    {
        return array(
            'paths_base_url'      => null,
            'multiline_arguments' => true,
        );
    }

    /**
     * Get HTML template.
     *
     * @return string
     */
    protected function getHtmlTemplate()
    {
        return
            '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
            <html xmlns ="http://www.w3.org/1999/xhtml">
            <head>
                <meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
                <title>Behat Test Suite</title>
                <style type="text/css">
            ' . $this->getHtmlTemplateStyle() . '
    </style>

    <style type="text/css" media="print">
' . $this->getHtmlTemplatePrintStyle() . '
    </style>
</head>
<body>
    <div id="behat">
        {{content}}
    </div>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.3/jquery.min.js"></script>
    <script type="text/javascript">
' . $this->getHtmlTemplateScript() . '
    </script>
</body>
</html>';
    }

    /**
     * Get HTML template style.
     *
     * @return string
     */
    protected function getHtmlTemplateStyle()
    {
        return <<<'HTMLTPL'
        body {
            margin:0px;
            padding:0px;
            position:relative;
            padding-top:75px;
        }
        #behat {
            float:left;
            font-family: Georgia, serif;
            font-size:18px;
            line-height:26px;
            width:100%;
        }
        #behat .statistics {
            float:left;
            width:100%;
            margin-bottom:15px;
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
        #behat .path a:link,
        #behat .path a:visited {
            color:#999;
        }
        #behat .path a:hover,
        #behat .path a:active {
            background-color:#000;
            color:#fff;
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
            margin-bottom:20px;
        }
        #behat .scenario > ol,
        #behat .scenario .examples > ol {
            margin:0px;
            list-style:none;
            padding:0px;
        }
        #behat .scenario > ol {
            margin-left:20px;
        }
        #behat .scenario > ol:after,
        #behat .scenario .examples > ol:after {
            content:'';
            display:block;
            clear:both;
        }
        #behat .scenario > ol li,
        #behat .scenario .examples > ol li {
            float:left;
            width:95%;
            padding-left:5px;
            border-left:5px solid;
            margin-bottom:4px;
        }
        #behat .scenario > ol li .argument,
        #behat .scenario .examples > ol li .argument {
            margin:10px 20px;
            font-size:16px;
            overflow:hidden;
        }
        #behat .scenario > ol li table.argument,
        #behat .scenario .examples > ol li table.argument {
            border:1px solid #d2d2d2;
        }
        #behat .scenario > ol li table.argument thead td,
        #behat .scenario .examples > ol li table.argument thead td {
            font-weight: bold;
        }
        #behat .scenario > ol li table.argument td,
        #behat .scenario .examples > ol li table.argument td {
            padding:5px 10px;
            background:#f3f3f3;
        }
        #behat .scenario > ol li .keyword,
        #behat .scenario .examples > ol li .keyword {
            font-weight:bold;
        }
        #behat .scenario > ol li .path,
        #behat .scenario .examples > ol li .path {
            float:right;
        }
        #behat .scenario .examples {
            margin-top:20px;
            margin-left:40px;
        }
        #behat .scenario .examples h4 span {
            font-weight:normal;
            background:#f3f3f3;
            color:#999;
            padding:0 5px;
            margin-left:10px;
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
            line-height:18px;
            color:#000;
            overflow:hidden;
            margin-left:20px;
            padding:15px;
            border-left:2px solid #C20000;
            background: #fff;
            margin-right:15px;
        }
        .stdout {
            font-size:12px;
            line-height:18px;
            color:#000;
            overflow:hidden;
            margin-left:20px;
            padding:15px;
            border-left:2px solid #000;
            background: #fff;
            margin-right:15px;
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
            position: absolute;
            top: 0px;
            left: 0px;
            width:100%;
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 18px;
        }
        #behat .summary .counters {
            padding: 10px;
            border-top: 0px;
            border-bottom: 0px;
            border-right: 0px;
            border-left: 5px;
            border-style: solid;
            height: 52px;
            overflow: hidden;
        }
        #behat .summary .switchers {
            position: absolute;
            right: 15px;
            top: 25px;
        }
        #behat .summary .switcher {
            text-decoration: underline;
            cursor: pointer;
        }
        #behat .summary .switchers a {
            margin-left: 10px;
            color: #000;
        }
        #behat .summary .switchers a:hover {
            text-decoration:none;
        }
        #behat .summary p {
            margin:0px;
        }
        #behat .jq-toggle > .scenario,
        #behat .jq-toggle > ol,
        #behat .jq-toggle > .examples {
            display:none;
        }
        #behat .jq-toggle-opened > .scenario,
        #behat .jq-toggle-opened > ol,
        #behat .jq-toggle-opened > .examples {
            display:block;
        }
        #behat .jq-toggle > h2,
        #behat .jq-toggle > h3 {
            cursor:pointer;
        }
        #behat .jq-toggle > h2:after,
        #behat .jq-toggle > h3:after {
            content:' |+';
            font-weight:bold;
        }
        #behat .jq-toggle-opened > h2:after,
        #behat .jq-toggle-opened > h3:after {
            content:' |-';
            font-weight:bold;
        }
HTMLTPL;
    }

    /**
     * Get HTML template style.
     *
     * @return string
     */
    protected function getHtmlTemplatePrintStyle()
    {
        return <<<'HTMLTPL'
        body {
            padding:0px;
        }

        #behat {
            font-size:11px;
        }

        #behat .jq-toggle > .scenario,
        #behat .jq-toggle > .scenario .examples,
        #behat .jq-toggle > ol {
            display:block;
        }

        #behat .summary {
            position:relative;
        }

        #behat .summary .counters {
            border:none;
        }

        #behat .summary .switchers {
            display:none;
        }

        #behat .step .path {
            display:none;
        }

        #behat .jq-toggle > h2:after,
        #behat .jq-toggle > h3:after {
            content:'';
            font-weight:bold;
        }

        #behat .jq-toggle-opened > h2:after,
        #behat .jq-toggle-opened > h3:after {
            content:'';
            font-weight:bold;
        }

        #behat .scenario > ol li,
        #behat .scenario .examples > ol li {
            border-left:none;
        }
HTMLTPL;
    }

    /**
     * Get HTML template script.
     *
     * @return string
     */
    protected function getHtmlTemplateScript()
    {
        return <<<'HTMLTPL'
        $(document).ready(function(){
            $('#behat .feature h2').click(function(){
                $(this).parent().toggleClass('jq-toggle-opened');
            }).parent().addClass('jq-toggle');

            $('#behat .scenario h3').click(function(){
                $(this).parent().toggleClass('jq-toggle-opened');
            }).parent().addClass('jq-toggle');

            $('#behat_show_all').click(function(){
                $('#behat .feature').addClass('jq-toggle-opened');
                $('#behat .scenario').addClass('jq-toggle-opened');
            });

            $('#behat_hide_all').click(function(){
                $('#behat .feature').removeClass('jq-toggle-opened');
                $('#behat .scenario').removeClass('jq-toggle-opened');
            });

            $('#behat .summary .counters .scenarios .passed')
                .addClass('switcher')
                .click(function(){
                    var $scenario = $('.feature .scenario:not(:has(.failed, .pending))');
                    var $feature  = $scenario.parent();

                    $('#behat_hide_all').click();

                    $scenario.addClass('jq-toggle-opened');
                    $feature.addClass('jq-toggle-opened');
                });

            $('#behat .summary .counters .steps .passed')
                .addClass('switcher')
                .click(function(){
                    var $scenario = $('.feature .scenario:has(.passed)');
                    var $feature  = $scenario.parent();

                    $('#behat_hide_all').click();

                    $scenario.addClass('jq-toggle-opened');
                    $feature.addClass('jq-toggle-opened');
                });

            $('#behat .summary .counters .failed')
                .addClass('switcher')
                .click(function(){
                    var $scenario = $('.feature .scenario:has(.failed)');
                    var $feature = $scenario.parent();

                    $('#behat_hide_all').click();

                    $scenario.addClass('jq-toggle-opened');
                    $feature.addClass('jq-toggle-opened');
                });

            $('#behat .summary .counters .skipped')
                .addClass('switcher')
                .click(function(){
                    var $scenario = $('.feature .scenario:has(.skipped)');
                    var $feature = $scenario.parent();

                    $('#behat_hide_all').click();

                    $scenario.addClass('jq-toggle-opened');
                    $feature.addClass('jq-toggle-opened');
                });

            $('#behat .summary .counters .pending')
                .addClass('switcher')
                .click(function(){
                    var $scenario = $('.feature .scenario:has(.pending)');
                    var $feature = $scenario.parent();

                    $('#behat_hide_all').click();

                    $scenario.addClass('jq-toggle-opened');
                    $feature.addClass('jq-toggle-opened');
                });
        });
HTMLTPL;
    }
}
