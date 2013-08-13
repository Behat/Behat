<?php

namespace Behat\Behat\Gherkin\Support;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Gherkin\Keywords\KeywordsDumper;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Story syntax printer.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class SyntaxPrinter
{
    private $dumper;

    /**
     * Initializes definition dispatcher.
     *
     * @param KeywordsDumper $dumper
     */
    public function __construct(KeywordsDumper $dumper)
    {
        $dumper->setKeywordsDumperFunction(array($this, 'dumpKeywords'));
        $this->dumper = $dumper;
    }

    /**
     * Prints example story syntax into console.
     *
     * @param OutputInterface $output
     * @param string          $language
     */
    public function printSyntax(OutputInterface $output, $language = 'en')
    {
        $output->getFormatter()->setStyle('gherkin_comment', new OutputFormatterStyle('yellow'));
        $output->getFormatter()->setStyle('gherkin_keyword', new OutputFormatterStyle('green', null, array('bold')));

        $story = $this->dumper->dump($language);
        $story = preg_replace('/^\#.*/', '<gherkin_comment>$0</gherkin_comment>', $story);

        $output->writeln($story);
    }

    /**
     * Keywords dumper.
     *
     * @param array $keywords keywords list
     *
     * @return string
     */
    public function dumpKeywords(array $keywords)
    {
        $dump = '<gherkin_keyword>' . implode('</gherkin_keyword>|<gherkin_keyword>', $keywords) . '</gherkin_keyword>';

        if (1 < count($keywords)) {
            return '[' . $dump . ']';
        }

        return $dump;
    }
}
