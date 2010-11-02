<?php

namespace Everzet\Behat\Output\Formatter;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Translation\TranslatorInterface;

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
use Everzet\Behat\Tester\StepTester;

/*
 * This file is part of the Behat.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * HTML Formatter.
 * Implements HTML output formatter.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class HTMLFormatter implements FormatterInterface, TranslatableFormatterInterface, ContainerAwareFormatterInterface
{
    protected $supportPath;
    protected $translator;
    protected $container;

    protected $html = '';
    protected $statuses;

    protected $backgroundPrinted            = false;
    protected $outlineStepsPrinted          = false;
    protected $outlineSubresultExceptions   = array();

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
     * @see     TranslatableFormatterInterface 
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @see     FormatterInterface
     */
    public function registerListeners(EventDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;

        $dispatcher->connect('feature.run.before',      array($this, 'printFeatureHeader'),     10);
        $dispatcher->connect('feature.run.after',       array($this, 'printFeatureFooter'),     10);

        $dispatcher->connect('outline.run.before',      array($this, 'printOutlineHeader'),     10);
        $dispatcher->connect('outline.sub.run.after',   array($this, 'printOutlineSubResult'),  10);
        $dispatcher->connect('outline.run.after',       array($this, 'printOutlineFooter'),     10);

        $dispatcher->connect('scenario.run.before',     array($this, 'printScenarioHeader'),    10);
        $dispatcher->connect('scenario.run.after',      array($this, 'printScenarioFooter'),    10);

        $dispatcher->connect('background.run.before',   array($this, 'printBackgroundHeader'),  10);
        $dispatcher->connect('background.run.after',    array($this, 'printBackgroundFooter'),  10);

        $dispatcher->connect('step.run.after',          array($this, 'printStep'),              10);

        $dispatcher->connect('suite.run.after',         array($this, 'flushHTML'),              10);
    }

    /**
     * Listen to `feature.run.before` event & print feature header.
     *
     * @param   Event   $event  notified event
     */
    public function printFeatureHeader(Event $event)
    {
        $feature = $event->getSubject();

        $this->html .= '<div class="feature">';

        // Print tags if had ones
        $this->html .= $this->getTagsHtml($feature);

        // Print feature header
        $this->html .= '<h2>';
        $this->html .= $this->getKeywordHtml('Feature', $feature->getLocale());
        $this->html .= $this->getTitleHtml($feature->getTitle());
        $this->html .= '</h2>';

        // Print feature description
        if ($feature->hasDescription()) {
            $this->html .= '<p>';
            foreach ($feature->getDescription() as $line) {
                $this->html .= htmlspecialchars($line) . '<br/>';
            }
            $this->html .= '</p>';
        }

        // Run fake background to test if it runs without errors & print it output
        if ($feature->hasBackground()) {
            $this->container->get('behat.statistics_collector')->pause();
            $tester = $this->container->get('behat.background_tester');
            $tester->setEnvironment($this->container->get('behat.environment'));
            $feature->getBackground()->accept($tester);
            $this->container->get('behat.statistics_collector')->resume();
            $this->backgroundPrinted = true;
        }
    }

    /**
     * Listen to `feature.run.after` event & print feature footer.
     *
     * @param   Event   $event  notified event
     */
    public function printFeatureFooter(Event $event)
    {
        $this->html .= '</div>';
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

        $this->html .= '<div class="scenario outline">';

        // Print tags if had ones
        $this->html .= $this->getTagsHtml($outline);

        // Print outline header
        $this->html .= '<h3>';
        $this->html .= $this->getKeywordHtml('Scenario Outline', $outline->getLocale());
        $this->html .= $this->getTitleHtml($outline->getTitle());
        $this->html .= $this->getSourcePathHtml($outline->getFile(), $outline->getLine());
        $this->html .= '</h3>';

        // Print outline steps
        $environment = $this->container->get('behat.environment');
        $this->container->get('behat.statistics_collector')->pause();
        $this->html .= '<ol>';
        foreach ($outline->getSteps() as $step) {
            $tester = $this->container->get('behat.step_tester');
            $tester->setEnvironment($environment);
            $tester->setTokens(current($examples->getHash()));
            $tester->skip();
            $step->accept($tester);
        }
        $this->html .= '</ol>';
        $this->container->get('behat.statistics_collector')->resume();
        $this->outlineStepsPrinted = true;

        $this->html .= '<div class="examples">';

        // Print outline examples title
        $this->html .= '<h4>';
        $this->html .= $this->getKeywordHtml('Examples', $outline->getLocale());
        $this->html .= '</h4>';

        // Print outline examples header row
        $this->html .= '<table>';
        $this->html .= '<thead>';
        $this->html .= $this->getTableRow($examples->getRow(0), 'skipped');
        $this->html .= '</thead>';
        $this->html .= '<tbody>';
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

        // Print current scenario results row
        $this->html .= $this->getTableRow(
            $examples->getRow($event->getParameter('iteration') + 1), $this->statuses[$event->getParameter('result')]
        );

        // Print errors
        foreach ($this->outlineSubresultExceptions as $exception) {
            $this->html .= '<tr class="failed exception">';
            $this->html .= '<td colspan="' . count($examples->getRow(0)) . '"><pre class="backtrace">' . htmlspecialchars($exception->getMessage()) . '</pre></td>';
            $this->html .= '</tr>';
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
        $this->html .= '</tbody>';
        $this->html .= '</table>';
        $this->html .= '</div>';
        $this->html .= '</div>';
    }

    /**
      * Listen to `scenario.run.before` event & print scenario header.
      *
      * @param   Event   $event  notified event
      */
    public function printScenarioHeader(Event $event)
    {
        $scenario = $event->getSubject();

        $this->html .= '<div class="scenario">';

        // Print tags if had ones
        $this->html .= $this->getTagsHtml($scenario);

        // Print scenario header
        $this->html .= '<h3>';
        $this->html .= $this->getKeywordHtml('Scenario', $scenario->getLocale());
        $this->html .= $this->getTitleHtml($scenario->getTitle());
        $this->html .= $this->getSourcePathHtml($scenario->getFile(), $scenario->getLine());
        $this->html .= '</h3>';

        $this->html .= '<ol>';
    }

    /**
      * Listen to `scenario.run.after` event & print scenario footer.
      *
      * @param   Event   $event  notified event
      */
    public function printScenarioFooter(Event $event)
    {
        $this->html .= '</ol>';
        $this->html .= '</div>';
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

            $this->html .= '<div class="scenario background">';

            // Print tags if had ones
            $this->html .= $this->getTagsHtml($background);

            // Print background header
            $this->html .= '<h3>';
            $this->html .= $this->getKeywordHtml('Background', $background->getLocale());
            $this->html .= $this->getTitleHtml($background->getTitle());
            $this->html .= $this->getSourcePathHtml($background->getFile(), $background->getLine());
            $this->html .= '</h3>';

            $this->html .= '<ol>';
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
            $this->html .= '</ol>';
            $this->html .= '</div>';
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
                $this->html .= '<li class="' . $this->statuses[$event->getParameter('result')] . '">';

                // Get step description
                $text = htmlspecialchars($this->outlineStepsPrinted ? $step->getText() : $step->getCleanText());

                // Print step
                $this->html .= '<div class="step">';
                $this->html .= '<span class="keyword">' . $step->getType() . '</span> ';
                $this->html .= '<span class="text">' . $text . '</span>';
                if (null !== ($def = $event->getParameter('definition'))) {
                    $this->html .= $this->getSourcePathHtml($def->getFile(), $def->getLine());
                }
                $this->html .= '</div>';

                // Print step arguments
                if ($step->hasArguments()) {
                    foreach ($step->getArguments() as $argument) {
                        if ($argument instanceof PyStringNode) {
                            $this->html .= '<pre class="argument">' . htmlspecialchars($argument) . '</pre>';
                        } elseif ($argument instanceof TableNode) {
                            $this->html .= $this->getTableHtml($argument, 'argument');
                        }
                    }
                }

                // Print step exception
                if (null !== $event->getParameter('exception')) {
                    $message    = $event->getParameter('exception')->getMessage();

                    $this->html .= '<div class="backtrace"><pre>' . htmlspecialchars($message) . '</pre></div>';
                }

                // Print step snippet
                if (null !== $event->getParameter('snippet')) {
                    $snippets = array_values($event->getParameter('snippet'));
                    $snippet = $snippets[0];

                    $this->html .= '<div class="snippet"><pre>' . htmlspecialchars($snippet) . '</pre></div>';
                }

                $this->html .= '</li>';
            } else {
                if (null !== $event->getParameter('exception')) {
                    $this->outlineSubresultExceptions[] = $event->getParameter('exception');
                }
            }
        }
    }

    /**
     * Listen to `suite.run.after` event & print generated markup to console or file. 
     * 
     * @param   Event   $event 
     */
    public function flushHTML(Event $event)
    {
        $statistics = $event->getSubject()->get('behat.statistics_collector');
        $html = '<div class="statistics ' . ($statistics->isPassed() ? 'passed' : 'failed') . '">';

        $html .= '<p class="scenarios">';
        $html .= $this->translator->transChoice(
            '{0} No scenarios|{1} 1 scenario|]1,Inf] %1% scenarios'
            , $statistics->getScenariosCount()
            , array('%1%' => $statistics->getScenariosCount())
        );
        $statuses = array();
        foreach ($statistics->getScenariosStatuses() as $status => $count) {
            if ($count) {
                $statuses[] = $this->translator->transChoice(
                    "[1,Inf] %1% $status"
                  , $count
                  , array('%1%' => $count)
                );
            }
        }
        $html .= count($statuses) ? ' ' . sprintf('(%s)', implode(', ', $statuses)) : '';
        $html .= '</p>';

        $html .= '<p class="steps">';
        $html .= $this->translator->transChoice(
            '{0} No steps|{1} 1 step|]1,Inf] %1% steps'
            , $statistics->getStepsCount()
            , array('%1%' => $statistics->getStepsCount())
        );
        $statuses = array();
        foreach ($statistics->getStepsStatuses() as $status => $count) {
            if ($count) {
                $statuses[] = $this->translator->transChoice(
                    "[1,Inf] %1% $status"
                  , $count
                  , array('%1%' => $count)
                );
            }
        }
        $html .= count($statuses) ? ' ' . sprintf('(%s)', implode(', ', $statuses)) : '';
        $html .= '</p>';
        $html .= '</div>';

        $html = strtr($this->getHTMLTemplate(), array('{{ body }}' => $html . $this->html));
        $event = new Event($this, 'behat.output.write', array('string' => $html, 'newline' => false));
        $this->dispatcher->notify($event);
    }

    /**
     * Get node tags HTML representation. 
     * 
     * @param   Node    $node 
     *
     * @return  string  HTML
     */
    protected function getTagsHtml($node)
    {
        $html = '';

        if ($node->hasTags()) {
            $html .= '<ul class="tags">';
            foreach ($node->getTags() as $tag) {
                $html .= '<li>@' . $tag . '</li>';
            }
            $html .= '</ul>';
        }

        return $html;
    }

    /**
     * Get keyword HTML representation. 
     * 
     * @param   string  $keyword    keyword
     * @param   string  $locale     locale
     *
     * @return  string              HTML
     */
    protected function getKeywordHtml($keyword, $locale = 'en')
    {
        $html  = '<span class="keyword">';
        $html .= $this->translator->trans($keyword, array(), 'messages', $locale) . ': ';
        $html .= '</span>';

        return $html;
    }

    /**
     * Get title HTML representation. 
     * 
     * @param   string  $title  title
     *
     * @return  string          HTML
     */
    protected function getTitleHtml($title)
    {
        return '<span class="title">' . $title . '</span>';
    }

    /**
     * Get source HTML representation. 
     * 
     * @param   string  $file   filename
     * @param   string  $line   lineno
     *
     * @return  string          HTML
     */
    protected function getSourcePathHtml($file, $line)
    {
        $html = '';

        if (null !== $file) {
            $file = ConsoleFormatter::trimFilename($file);

            $html .= '<span class="path">';
            $html .= $file . (null !== $line ? ':' . $line : '');
            $html .= '</span>';
        }

        return $html;
    }

    /**
     * Get TableNode HTML representation. 
     * 
     * @param   TableNode   $table  table node
     * @param   string      $class  optional css class
     *
     * @return  string              HTML
     */
    protected function getTableHtml(TableNode $table, $class = null)
    {
        if (null === $class) {
            $html = '<table><tbody>';
        } else {
            $html = '<table class="' . $class . '"><tbody>';
        }

        foreach ($table->getHash() as $row) {
            $html .= $this->getTableRow($row);
        }

        $html .= '</tbody></table>';

        return $html;
    }

    /**
     * Get table row HTML representation. 
     * 
     * @param   array   $row    table row as array
     * @param   string  $class  optional css class
     * 
     * @return  string          HTML
     */
    protected function getTableRow(array $row, $class = null)
    {
        if (null === $class) {
            $html = '<tr>';
        } else {
            $html = '<tr class="' . $class . '">';
        }

        foreach ($row as $col) {
            $html .= '<td>' . $col . '</td>';
        }
        $html .= '</tr>';

        return $html;
    }

    /**
     * Get HTML body template for the output. 
     * 
     * @return  string  HTML
     */
    protected function getHTMLTemplate()
    {
        if (is_file($tpl = $this->supportPath . '/templates/html.tpl')) {
            return file_get_contents($tpl);
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
    </style>
</head>
<body>
    <div id="behat">
        {{ body }}
    </div>
</body>
</html>
HTMLTPL;
    }
}
