<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition\Cli\Printer;

use Behat\Behat\Definition\Definition;
use Behat\Behat\Definition\Pattern\PatternTransformer;
use Behat\Behat\Definition\Printer\DefinitionPrinter;
use Behat\Testwork\Printer\OutputPrinter;
use Behat\Testwork\Suite\Suite;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Behat abstract definition printer.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class AbstractDefinitionPrinter implements DefinitionPrinter
{
    /**
     * @var OutputPrinter
     */
    private $printer;
    /**
     * @var PatternTransformer
     */
    private $patternTransformer;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * Initializes printer.
     *
     * @param OutputPrinter       $printer
     * @param PatternTransformer  $patternTransformer
     * @param TranslatorInterface $translator
     */
    public function __construct(
        OutputPrinter $printer,
        PatternTransformer $patternTransformer,
        TranslatorInterface $translator
    ) {
        $this->printer = $printer;
        $this->patternTransformer = $patternTransformer;
        $this->translator = $translator;

        $printer->setOutputStyles(
            array(
                'def_regex'         => array('yellow'),
                'def_regex_capture' => array('yellow', null, array('bold')),
                'def_dimmed'        => array('black', null, array('bold'))
            )
        );
    }

    /**
     * Writes text to the console.
     *
     * @param string $text
     */
    protected function write($text)
    {
        $this->printer->writeln($text);
        $this->printer->writeln();
    }

    /**
     * Returns definition regex translated into provided language.
     *
     * @param Suite      $suite
     * @param Definition $definition
     *
     * @return string
     */
    protected function getDefinitionPattern(Suite $suite, Definition $definition)
    {
        return $this->translator->trans($definition->getPattern(), array(), $suite->getName());
    }
}
