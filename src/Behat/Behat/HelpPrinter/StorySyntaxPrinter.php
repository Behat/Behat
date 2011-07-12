<?php

namespace Behat\Behat\HelpPrinter;

use Behat\Gherkin\Keywords\KeywordsDumper;

use Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Console\Formatter\OutputFormatterStyle;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Story syntax printer.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class StorySyntaxPrinter
{
    /**
     * Keywords dumper.
     *
     * @var     Behat\Gherkin\Keywords\KeywordsDumper
     */
    private $dumper;

    /**
     * Initializes definition dispatcher.
     *
     * @param   Behat\Gherkin\Keywords\KeywordsDumper   $dumper
     */
    public function __construct(KeywordsDumper $dumper)
    {
        $this->dumper = $dumper;
    }

    /**
     * Prints example story syntax into console.
     *
     * @param   Symfony\Component\Console\Output\OutputInterface    $output
     * @param   string                                              $language
     */
    public function printSyntax(OutputInterface $output, $language = 'en')
    {
        $output->getFormatter()->setStyle('comment', new OutputFormatterStyle('yellow'));
        $output->getFormatter()->setStyle(
            'keyword', new OutputFormatterStyle('green', null, array('bold'))
        );

        $story = explode("\n", $this->dumper->dump($language));

        foreach ($story as $num => $line) {
            $line = preg_replace('/^\#.*/', '<comment>$0</comment>', $line);
            $line = preg_replace('/^(\s*[^\#\(]*)\:/', '<keyword>$1</keyword>:', $line);
            $line = preg_replace_callback('/^(\s*)\(([^\)]*)\)\:/', function($match) {
                $indent     = $match[1];
                $keywords   = explode('|', $match[2]);

                foreach ($keywords as $num => $keyword) {
                    $keywords[$num] = "<keyword>$keyword</keyword>";
                }

                return "{$indent}[" . implode(', ', $keywords) . ']:';
            }, $line);
            $line = preg_replace_callback('/^(\s*)\(([^\)]*)\)\s/', function($match) {
                $indent     = $match[1];
                $keywords   = explode('|', $match[2]);

                foreach ($keywords as $num => $keyword) {
                    $keywords[$num] = "<keyword>$keyword</keyword>";
                }

                return "{$indent}[" . implode(', ', $keywords) . '] ';
            }, $line);

            $story[$num] = $line;
        }

        $output->writeln(implode("\n", $story));
    }
}
