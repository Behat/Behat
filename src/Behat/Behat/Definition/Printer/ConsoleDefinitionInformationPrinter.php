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
use Behat\Testwork\Suite\Suite;

/**
 * Prints definitions with full information about them.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ConsoleDefinitionInformationPrinter extends ConsoleDefinitionPrinter
{
    /**
     * @var null|string
     */
    private $searchCriterion;

    /**
     * Sets search criterion.
     *
     * @param string $criterion
     */
    public function setSearchCriterion($criterion)
    {
        $this->searchCriterion = $criterion;
    }

    /**
     * {@inheritdoc}
     */
    public function printDefinitions(Suite $suite, $definitions)
    {
        $search = $this->searchCriterion;
        $output = array();

        foreach ($definitions as $definition) {
            $definition = $this->translateDefinition($suite, $definition);
            $pattern = $definition->getPattern();

            if (null !== $search && false === mb_strpos($pattern, $search, 0, 'utf8')) {
                continue;
            }

            $lines = array_merge(
                $this->extractHeader($suite, $definition),
                $this->extractDescription($suite, $definition),
                $this->extractFooter($suite, $definition)
            );

            $output[] = implode(PHP_EOL, $lines) . PHP_EOL;
        }

        $this->write(rtrim(implode(PHP_EOL, $output)));
    }

    /**
     * Extracts the formatted header from the definition.
     *
     * @param Suite      $suite
     * @param Definition $definition
     *
     * @return string[]
     */
    private function extractHeader(Suite $suite, Definition $definition)
    {
        $pattern = $definition->getPattern();
        $lines = array();
        $lines[] = strtr(
            '{suite} <def_dimmed>|</def_dimmed> <info>{type}</info> <def_regex>{regex}</def_regex>', array(
                '{suite}' => $suite->getName(),
                '{type}'  => $this->getDefinitionType($definition),
                '{regex}' => $pattern,
            )
        );

        return $lines;
    }

    /**
     * Extracts the formatted description from the definition.
     *
     * @param Suite      $suite
     * @param Definition $definition
     *
     * @return string[]
     */
    private function extractDescription(Suite $suite, Definition $definition)
    {
        $definition = $this->translateDefinition($suite, $definition);

        $lines = array();
        if ($description = $definition->getDescription()) {
            foreach (explode("\n", $description) as $descriptionLine) {
                $lines[] = strtr(
                    '{space}<def_dimmed>|</def_dimmed> {description}', array(
                        '{space}'       => str_pad('', mb_strlen($suite->getName(), 'utf8') + 1),
                        '{description}' => $descriptionLine
                    )
                );
            }
        }

        return $lines;
    }

    /**
     * Extracts the formatted footer from the definition.
     *
     * @param Suite      $suite
     * @param Definition $definition
     *
     * @return string[]
     */
    private function extractFooter(Suite $suite, Definition $definition)
    {
        $lines = array();
        $lines[] = strtr(
            '{space}<def_dimmed>|</def_dimmed> at `{path}`', array(
                '{space}' => str_pad('', mb_strlen($suite->getName(), 'utf8') + 1),
                '{path}'  => $definition->getPath()
            )
        );

        if ($this->isVerbose()) {
            $lines[] = strtr(
                '{space}<def_dimmed>|</def_dimmed> on `{filepath}[{start}:{end}]`', array(
                    '{space}' => str_pad('', mb_strlen($suite->getName(), 'utf8') + 1),
                    '{filepath}' => $definition->getReflection()->getFileName(),
                    '{start}' => $definition->getReflection()->getStartLine(),
                    '{end}' => $definition->getReflection()->getEndLine()
                )
            );
        }

        return $lines;
    }
}
