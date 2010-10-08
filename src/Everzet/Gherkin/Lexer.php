<?php

namespace Everzet\Gherkin;

use Symfony\Component\Translation\TranslatorInterface;

/*
 * This file is part of the Gherkin.
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
    protected $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getTagKeyword()
    {
        return '@';
    }

    public function getTableSplitter()
    {
        return '|';
    }

    public function getStepTypes()
    {
        return explode('|', $this->translator->trans('Given|Then|When|And|But'));
    }

    public function getFeatureKeyword()
    {
        return $this->translator->trans('Feature');
    }

    public function getBackgroundKeyword()
    {
        return $this->translator->trans('Background');
    }

    public function getScenarioKeyword()
    {
        return $this->translator->trans('Scenario');
    }

    public function getScenarioOutlineKeyword()
    {
        return $this->translator->trans('Scenario Outline');
    }

    public function getExamplesKeyword()
    {
        return $this->translator->trans('Examples');
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

