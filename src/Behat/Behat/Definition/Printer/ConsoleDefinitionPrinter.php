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
    public function __construct(
        private readonly OutputInterface $output,
        private readonly DefinitionTranslator $translator,
        private readonly KeywordsInterface $keywords,
    ) {
        $this->output->getFormatter()->setStyle('def_regex', new OutputFormatterStyle('yellow'));
        $this->output->getFormatter()->setStyle(
            'def_regex_capture',
            new OutputFormatterStyle('yellow', null, ['bold'])
        );
        $this->output->getFormatter()->setStyle(
            'def_dimmed',
            new OutputFormatterStyle('black', null, ['bold'])
        );
    }

    /**
     * Writes text to the console.
     *
     * @param string $text
     */
    final protected function write($text, bool $lineBreakBefore = false)
    {
        if ($lineBreakBefore) {
            $this->output->writeln('');
        }
        $this->output->writeln($text);
        $this->output->writeln('');
    }

    final protected function getDefinitionType(Definition $definition, $onlyOne = false)
    {
        $this->keywords->setLanguage($this->translator->getLocale());

        $method = 'get' . ucfirst($definition->getType()) . 'Keywords';

        $keywords = explode('|', (string) $this->keywords->$method());

        if ($onlyOne) {
            return current($keywords);
        }

        return 1 < count($keywords) ? '[' . implode('|', $keywords) . ']' : implode('|', $keywords);
    }

    /**
     * Translates definition using translator.
     *
     * @return Definition
     */
    final protected function translateDefinition(Suite $suite, Definition $definition)
    {
        return $this->translator->translateDefinition($suite, $definition);
    }

    final protected function translateInfoText(string $infoText, array $parameters): string
    {
        return $this->translator->translateInfoText($infoText, $parameters);
    }

    /**
     * Returns whether verbosity is verbose (-v).
     *
     * @return bool true if verbosity is set to VERBOSITY_VERBOSE, false otherwise
     */
    final protected function isVerbose()
    {
        return $this->output->isVerbose();
    }
}
