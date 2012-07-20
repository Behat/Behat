<?php

namespace Behat\Behat\HelpPrinter;

use Behat\Behat\Definition\DefinitionDispatcher;

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
 * Definitions printer.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class DefinitionsPrinter
{
    private $dispatcher;

    /**
     * Initializes definition dispatcher.
     *
     * @param DefinitionDispatcher $dispatcher
     */
    public function __construct(DefinitionDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Prints step definitions into console.
     *
     * @param OutputInterface $output
     * @param string          $search
     * @param string          $language
     * @param Boolean         $shortNotation
     */
    public function printDefinitions(OutputInterface $output, $search = null, $language = 'en', $shortNotation = true)
    {
        $output->getFormatter()->setStyle(
            'capture', new OutputFormatterStyle('yellow', null, array('bold'))
        );
        $output->getFormatter()->setStyle(
            'path', new OutputFormatterStyle('black')
        );

        $output->writeln($this->getDefinitionsForPrint($search, $language, $shortNotation));
    }

    /**
     * Returns available definitions in string.
     *
     * @param string  $search        search string
     * @param string  $language      default definitions language
     * @param Boolean $shortNotation show short notation instead of full one
     *
     * @return string
     */
    private function getDefinitionsForPrint($search = null, $language = 'en', $shortNotation = true)
    {
        if ($shortNotation) {
            $template = '<info>{type}</info> <comment>{regex}</comment>';
        } else {
            $template = <<<TPL
<info>{type}</info> <comment>{regex}</comment>
    {description}<path># {path}</path>

TPL;
        }

        $definitions = array();
        foreach ($this->dispatcher->getDefinitions() as $regex => $definition) {
            $regex = $this->dispatcher->translateDefinitionRegex($regex, $language);
            if ($search && !preg_match('/'.str_replace(' ', '.*', preg_quote($search, '/').'/'), $regex)) {
                continue;
            }

            $regex = preg_replace_callback('/\((?!\?:)[^\)]*\)/', function($capture) {
                return "</comment><capture>{$capture[0]}</capture><comment>";
            }, $regex);

            $definitions[] = strtr($template, array(
                '{regex}'       => $regex,
                '{type}'        => str_pad($definition->getType(), 5, ' ', STR_PAD_LEFT),
                '{description}' => $definition->getDescription() ? '- '.$definition->getDescription()."\n    " : '',
                '{path}'        => $definition->getPath()
            ));
        }

        return implode("\n", $definitions);
    }
}
