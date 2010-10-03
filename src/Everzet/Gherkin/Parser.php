<?php

namespace Everzet\Gherkin;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/*
 * This file is part of the Gherkin.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Gherkin Parser.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Parser
{
    protected $file             = null;
    protected $translator       = null;
    protected $container        = null;
    protected $lines            = array();
    protected $currentLineNb    = -1;
    protected $currentLine      = '';
    protected $regex            = null;
    protected $feature          = null;

    public function __construct(Container $container = null)
    {
        if (null === $container) {
            $container  = new ContainerBuilder();
            $xmlLoader  = new XmlFileLoader($container);
            $xmlLoader->load(__DIR__ . '/ServiceContainer/container.xml');
        }

        $this->container    = $container;
        $this->translator   = $container->getTranslatorService();
    }

    public function parseFile($file)
    {
        $this->file = $file;
        $feature = $this->parse(file_get_contents($file));
        $this->file = null;

        return $feature;
    }

    public function parse($value)
    {
        $this->currentLineNb = -1;
        $this->currentLine = '';
        $this->lines = explode("\n", $this->cleanup($value));

        if (preg_match('#^\#\s*language\:\s*(?P<lang>[\w]+?)\s*$#', $this->lines[0], $values)) {
            $this->translator->setLocale($values['lang']);
        }
        $this->lexer = new Lexer($this->translator);

        if (function_exists('mb_internal_encoding') && ((int) ini_get('mbstring.func_overload')) & 2) {
            $mbEncoding = mb_internal_encoding();
            mb_internal_encoding('ASCII');
        }

        while ($this->moveToNextLine()) {
            if ($this->isCurrentLineEmpty()) {
                continue;
            }

            // feature?
            if (preg_match($this->lexer->getFeatureRegex(), $this->currentLine, $values)) {
                $class = $this->container->getParameter('feature_node.class');
                $this->feature = new $class($this->translator->getLocale(), $this->file);
                $this->feature->setTitle(isset($values['title']) ? $values['title'] : '');
                $this->feature->addTags($this->getPreviousTags());
                $this->feature->addDescriptions($this->getNextDescriptions());
            }

            // background?
            if (preg_match($this->lexer->getBackgroundRegex(), $this->currentLine, $values)) {
                $class = $this->container->getParameter('background_node.class');
                $background = new $class($this->translator->getLocale(), $this->file, $this->currentLineNb);
                $background->setTitle($this->getNextTitle(
                    isset($values['title']) ? $values['title'] : ''
                ));
                $background->setSteps($this->getNextSteps());

                $this->feature->setBackground($background);
            }

            // scenario?
            if (preg_match($this->lexer->getScenarioRegex(), $this->currentLine, $values)) {
                $class = $this->container->getParameter('scenario_node.class');
                $scenario = new $class($this->translator->getLocale(), $this->file, $this->currentLineNb);
                $scenario->setTitle($this->getNextTitle(
                    isset($values['title']) ? $values['title'] : ''
                ));
                $scenario->addTags($this->getPreviousTags());
                $scenario->setSteps($this->getNextSteps());

                $this->feature->addScenario($scenario);
            }

            // scenario outline?
            if (preg_match($this->lexer->getScenarioOutlineRegex(), $this->currentLine, $values)) {
                $class = $this->container->getParameter('outline_node.class');
                $outline = new $class($this->translator->getLocale(), $this->file, $this->currentLineNb);
                $outline->setTitle($this->getNextTitle(
                    isset($values['title']) ? $values['title'] : ''
                ));
                $outline->addTags($this->getPreviousTags());
                $outline->setSteps($this->getNextSteps());
                if (null === ($examples = $this->getNextExamples())) {
                    throw new ParserException(
                        sprintf('No examples in %s outline', $outline->getTitle())
                    );
                }
                $outline->setExamples($examples);

                $this->feature->addScenario($outline);
            }
        }

        if (isset($mbEncoding)) {
            mb_internal_encoding($mbEncoding);
        }

        return $this->feature;
    }

    protected function getPreviousTags()
    {
        $tags = array();

        if (preg_match($this->lexer->getTagsRegex(), $this->getPreviousLine(), $values)) {
            $tags = array_map(function($item) {
                return trim($item);
            }, explode($this->lexer->getTagKeyword(), $values['tags']));
        }

        return $tags;
    }

    protected function getNextTitle($title = '')
    {
        while($this->moveToNextLine()) {
            if ($this->isCurrentLineEmpty()) {
                continue;
            }
            if (
                preg_match($this->lexer->getStepsRegex(), $this->currentLine) ||
                preg_match($this->lexer->getTableRegex(), $this->currentLine)
            ) {
                break;
            }
            $title .= empty($title) ? trim($this->currentLine) : ' ' . trim($this->currentLine);
        }
        $this->moveToPreviousLine();

        return $title;
    }

    protected function getNextDescriptions()
    {
        $lines = array();

        while ($this->moveToNextLine()) {
            if ($this->isCurrentLineEmpty()) {
                continue;
            }
            if (!preg_match($this->lexer->getDescriptionRegex(), $this->currentLine, $values)) {
                break;
            }

            $description = trim($values['description']);
            if (!empty($description)) {
                $lines[] = $values['description'];
            }
        }
        $this->moveToPreviousLine();

        return $lines;
    }

    protected function getNextSteps()
    {
        $steps = array();

        while ($this->moveToNextLine()) {
            if ($this->isCurrentLineEmpty()) {
                continue;
            }
            if (!preg_match($this->lexer->getStepsRegex(), $this->currentLine, $values)) {
                break;
            }

            $class = $this->container->getParameter('step_node.class');
            $step = new $class($values['type'], $values['step'], $this->currentLineNb);
            if (null !== ($pystring = $this->getNextPyString())) {
                $step->addArgument($pystring);
            }
            if (null !== ($table = $this->getNextTable())) {
                $step->addArgument($table);
            }
            $steps[] = $step;
        }
        $this->moveToPreviousLine();

        return $steps;
    }

    protected function getNextPyString()
    {
        $pystring = null;

        if (
            $this->moveToNextLine() &&
            preg_match($this->lexer->getPyStringStarterRegex(), $this->currentLine, $values)
        ) {
            $class = $this->container->getParameter('pystring_node.class');
            $pystring = new $class(mb_strlen($values['indent']));

            while (
                $this->moveToNextLine() &&
                !preg_match($this->lexer->getPyStringStarterRegex(), $this->currentLine)
            ) {
                $pystring->addLine($this->currentLine);
            }
        } else {
            $this->moveToPreviousLine();
        }

        return $pystring;
    }

    protected function getNextTable()
    {
        $table = null;

        while ($this->moveToNextLine()) {
            if ($this->isCurrentLineEmpty()) {
                continue;
            }
            if (!preg_match($this->lexer->getTableRegex(), $this->currentLine, $values)) {
                break;
            }

            if (null === $table) {
                $class = $this->container->getParameter('table_node.class');
                $table = new $class($this->lexer->getTableSplitter());
            }
            $table->addRow($values['row']);
        }
        $this->moveToPreviousLine();

        return $table;
    }

    protected function getNextExamples()
    {
        $examples = null;

        while($this->moveToNextLine()) {
            if (!$this->isCurrentLineEmpty()) {
                break;
            }
        }
        if (preg_match($this->lexer->getExamplesRegex(), $this->currentLine, $values)) {
            $class = $this->container->getParameter('examples_node.class');
            $examples = new $class(
                $this->getNextTitle(isset($values['title']) ? $values['title'] : '')
            );
            $examples->setTable($this->getNextTable());
        }
        $this->moveToPreviousLine();

        return $examples;
    }

    protected function getPreviousLine()
    {
        if ($this->currentLineNb != 0) {
            return $this->lines[$this->currentLineNb - 1];
        } else {
            return $this->currentLine;
        }
    }

    protected function getNextLine()
    {
        if ($this->currentLineNb != count($this->lines)) {
            return $this->lines[$this->currentLineNb + 1];
        } else {
            return $this->currentLine;
        }
    }

    protected function moveToNextLine()
    {
        if ($this->currentLineNb >= count($this->lines) - 1) {
            return false;
        }

        $this->currentLine = $this->lines[++$this->currentLineNb];

        return true;
    }

    protected function moveToPreviousLine()
    {
        $this->currentLine = $this->lines[--$this->currentLineNb];
    }

    protected function isCurrentLineEmpty()
    {
        return $this->isCurrentLineBlank() || $this->isCurrentLineComment();
    }

    protected function isCurrentLineBlank()
    {
        return '' == trim($this->currentLine, ' ');
    }

    protected function isCurrentLineComment()
    {
        //checking explicitly the first char of the trim is faster than loops or strpos
        $ltrimmedLine = ltrim($this->currentLine, ' ');
        return $ltrimmedLine[0] === '#';
    }

    protected function cleanup($value)
    {
        $value = str_replace(array("\r\n", "\r"), "\n", $value);

        if (!preg_match("#\n$#", $value)) {
            $value .= "\n";
        }

        return $value;
    }
}
