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
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class DefinitionsPrinter
{
    /**
     * Definition dispatcher.
     *
     * @var     Behat\Behat\Definition\DefinitionDispatcher
     */
    private $dispatcher;

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

        $output->writeln($this->getDefinitionsForPrint($language, 'description'));
    }

    /**
     * Prints step definitions and their associated functions into console.
     *
     * @param   Symfony\Component\Console\Output\OutputInterface    $output
     * @param   string                                              $language
     */
    public function printDefinitionsFunctions(OutputInterface $output, $language = 'en')
    {
        $output->getFormatter()->setStyle(
            'capture', new OutputFormatterStyle('yellow', null, array('bold'))
        );

        $output->writeln($this->getDefinitionsForPrint($language, 'function'));
    }

    /**
     * Returns available definitions in string.
     *
     * @param   string  $language   default definitions language
     *
     * @return  string
     */
    private function getDefinitionsForPrint($language = 'en', $helpTextType)
    {
        $lineLength = 0;
        foreach ($this->dispatcher->getDefinitions() as $regex => $definition) {
            $regex = $this->dispatcher->translateDefinitionRegex($regex, $language);
            $lineLength = max($lineLength, mb_strlen($regex));
        }

        $definitions = array();
        foreach ($this->dispatcher->getDefinitions() as $regex => $definition) {
            $regex = $this->dispatcher->translateDefinitionRegex($regex, $language);
            $space = $lineLength - mb_strlen($regex);
            $regex = preg_replace_callback('/\([^\)]*\)/', function($capture) {
                return "</comment><capture>{$capture[0]}</capture><comment>";
            }, $regex);
            $type  = str_pad($definition->getType(), 5, ' ', STR_PAD_LEFT);

            $helpText = '';
            if ('description' === $helpTextType) {
                $helpText = $definition->getDescription() ? ' - ' . $definition->getDescription() : '';
            } elseif ('function' === $helpTextType) {
                $helpText = ' # ' . $definition->getPath();
            }

            $definitions[] = sprintf("%s %s%-${space}s%s",
                "<info>$type</info>",
                "<comment>$regex</comment>",
                '',
                $helpText
            );
        }

        return implode("\n", $definitions);
    }
}
