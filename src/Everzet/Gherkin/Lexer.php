<?php

namespace Everzet\Gherkin;

use Everzet\Gherkin\I18n;

/*
 * This file is part of the behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Regular expressions container.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Lexer
{
    protected $i18n;

    public function __construct(I18n $i18n)
    {
        $this->i18n = $i18n;
    }

    public function getTagKeyword()
    {
        return $this->i18n->__('tag-keyword', '@');
    }

    public function getTableSplitter()
    {
        return $this->i18n->__('table-splitter', '|');
    }

    public function getStepTypes()
    {
        return $this->i18n->__('step-types', array('Given', 'When', 'Then', 'And', 'But'));
    }

    public function getFeatureKeyword()
    {
        return $this->i18n->__('feature', 'Feature');
    }

    public function getBackgroundKeyword()
    {
        return $this->i18n->__('background', 'Background');
    }

    public function getScenarioKeyword()
    {
        return $this->i18n->__('scenario', 'Scenario');
    }

    public function getScenarioOutlineKeyword()
    {
        return $this->i18n->__('scenario-outline', 'Scenario Outline');
    }

    public function getExamplesKeyword()
    {
        return $this->i18n->__('examples', 'Examples');
    }

    private function getStepTypesRegex()
    {
        return implode('|', $this->getStepTypes());
    }

    private function getNotOneOfRegex(array $names)
    {
        return sprintf("(?!\\s*%s)", implode("|\\s*", $names));
    }

    public function getFeatureRegex()
    {
        return '#^\s*' . $this->getFeatureKeyword() . '\:\s*(?P<title>.+?)?\s*$#';
    }

    public function getBackgroundRegex()
    {
        return '#^\s*' . $this->getBackgroundKeyword() . '\:\s*(?P<title>.+?)?\s*$#';
    }

    public function getScenarioRegex()
    {
        return '#^\s*' . $this->getScenarioKeyword() . '\:(?:\s+(?P<title>.+?))?\s*$#';
    }

    public function getScenarioOutlineRegex()
    {
        return '#^\s*' . $this->getScenarioOutlineKeyword() . '\:(?:\s+(?P<title>.+?))?\s*$#';
    }

    public function getExamplesRegex()
    {
        return '#^\s*' . $this->getExamplesKeyword() . '\:(?:\s+(?P<title>.+?))?\s*$#';
    }

    public function getTagsRegex()
    {
        return '#^\s*' . "\\" . $this->getTagKeyword() . '(?P<tags>.+?)\s*$#';
    }

    public function getDescriptionRegex()
    {
        $isNotOneOfRegex = $this->getNotOneOfRegex(
            array_merge(
                array($this->getBackgroundKeyword(), $this->getScenarioOutlineKeyword(), 
                    $this->getScenarioKeyword(), "\\" . $this->getTagKeyword(),
                    "\\" . $this->getTableSplitter()
                ),
                $this->getStepTypes()
            )
        );

        return '#^\s*(?P<description>' . $isNotOneOfRegex . '.+?)\s*$#';
    }

    public function getStepsRegex()
    {
        return '#^\s*(?P<type>' . $this->getStepTypesRegex() . '?)\s+(?P<step>.+?)\s*$#';
    }

    public function getPyStringStarterRegex()
    {
        return '#^(?P<indent>\s*?)\"\"\"\s*$#';
    }

    public function getTableRegex()
    {
        return "#^\s*\\" . $this->getTableSplitter() . "(?P<row>.+?)\\" . $this->getTableSplitter() . "\s*$#";
    }
}
