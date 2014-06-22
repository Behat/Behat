<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition\Printer;

use Behat\Behat\Definition\Definition;
use Behat\Behat\Definition\Pattern\PatternTransformer;
use Behat\Behat\Definition\Translator\DefinitionTranslator;
use Behat\Gherkin\Keywords\KeywordsInterface;
use Behat\Testwork\Suite\Suite;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Represents console-based definition printer.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class ConsoleDefinitionPrinter implements DefinitionPrinter
{
    /**
     * @var OutputInterface
     */
    private $output;
    /**
     * @var PatternTransformer
     */
    private $patternTransformer;
    /**
     * @var DefinitionTranslator
     */
    private $translator;
    /**
     * @var KeywordsInterface
     */
    private $keywords;

    /**
     * Initializes printer.
     *
     * @param OutputInterface     $output
     * @param PatternTransformer  $patternTransformer
     * @param DefinitionTranslator $translator
     * @param KeywordsInterface   $keywords
     */
    public function __construct(
        OutputInterface $output,
        PatternTransformer $patternTransformer,
        DefinitionTranslator $translator,
        KeywordsInterface $keywords
    ) {
        $this->output = $output;
        $this->patternTransformer = $patternTransformer;
        $this->translator = $translator;
        $this->keywords = $keywords;

        $output->getFormatter()->setStyle('def_regex', new OutputFormatterStyle('yellow'));
        $output->getFormatter()->setStyle(
            'def_regex_capture',
            new OutputFormatterStyle('yellow', null, array('bold'))
        );
        $output->getFormatter()->setStyle(
            'def_dimmed',
            new OutputFormatterStyle('black', null, array('bold'))
        );
    }

    /**
     * Writes text to the console.
     *
     * @param string $text
     */
    final protected function write($text)
    {
        $this->output->writeln($text);
        $this->output->writeln('');
    }

    final protected function getDefinitionType(Definition $definition, $onlyOne = false)
    {
        $this->keywords->setLanguage($this->translator->getLocale());

        $method = 'get'.ucfirst($definition->getType()).'Keywords';

        $keywords = explode('|', $this->keywords->$method());

        if ($onlyOne) {
            return current($keywords);
        }

        return 1 < count($keywords) ? '['.implode('|', $keywords).']' : implode('|', $keywords);
    }

    /**
     * Translates definition using translator.
     *
     * @param Suite      $suite
     * @param Definition $definition
     *
     * @return Definition
     */
    final protected function translateDefinition(Suite $suite, Definition $definition)
    {
        return $this->translator->translateDefinition($suite, $definition);
    }
}
