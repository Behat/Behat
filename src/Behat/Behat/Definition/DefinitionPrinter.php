<?php

namespace Behat\Behat\Definition;

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
 * Definition dumper.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class DefinitionPrinter
{
    /**
     * Definition dispatcher.
     *
     * @var     Behat\Behat\Definition\DefinitionDispatcher
     */
    protected $dispatcher;

    /**
     * Initializes definition dispatcher.
     *
     * @param   Behat\Behat\Definition\DefinitionDispatcher $dispatcher
     */
    public function __construct(DefinitionDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Prints step definitions into console.
     *
     * @param   Symfony\Component\Console\Output\OutputInterface    $output
     * @param   string                                              $language
     */
    public function printDefinitions(OutputInterface $output, $language = 'en')
    {
        $output->getFormatter()->setStyle(
            'capture', new OutputFormatterStyle('yellow', null, array('bold'))
        );

        $output->write($this->getDefinitionsForPrint($language));
    }

    /**
     * Returns available definitions in string.
     *
     * @param   string  $language   default definitions language
     *
     * @return  string
     */
    private function getDefinitionsForPrint($language = 'en')
    {
        $definitions = '';

        foreach ($this->dispatcher->getDefinitions() as $regex => $definition) {
            $regex = $this->dispatcher->translateDefinitionRegex($regex, $language);
            $regex = preg_replace_callback('/\([^\)]*\)/', function($capture) {
                return "</comment><capture>{$capture[0]}</capture><comment>";
            }, $regex);
            $type  = str_pad($definition->getType(), 5, ' ', STR_PAD_LEFT);

            $definitions .= "<info>$type</info> <comment>$regex</comment>\n";
        }

        return $definitions;
    }
}
