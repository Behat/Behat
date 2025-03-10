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
final class ConsoleDefinitionInformationPrinter extends ConsoleDefinitionPrinter implements UnusedDefinitionPrinter
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
        $this->printDefinitionsWithOptionalSuite($definitions, $suite);
    }

    public function printUnusedDefinitions(array $definitions): void
    {
        $unusedDefinitionsText = $this->translateInfoText(
            'unused_definitions',
            ['%count%' => count($definitions)]
        );
        $this->write('--- ' . $unusedDefinitionsText, true);
        if (count($definitions) !== 0) {
            $this->printDefinitionsWithOptionalSuite($definitions);
        }
    }

    /**
     * @param Definition[] $definitions
     */
    private function printDefinitionsWithOptionalSuite(array $definitions, ?Suite $suite = null): void
    {
        $search = $this->searchCriterion;
        $output = [];

        foreach ($definitions as $definition) {
            if ($suite) {
                $definition = $this->translateDefinition($suite, $definition);
            }
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
     * @return string[]
     */
    private function extractHeader(?Suite $suite, Definition $definition): array
    {
        $pattern = $definition->getPattern();
        $lines = [];
        $indent = $suite ? '{suite} <def_dimmed>|</def_dimmed> ' : '';
        $lines[] = strtr(
            $indent . '<info>{type}</info> <def_regex>{regex}</def_regex>',
            [
                '{suite}' => $suite ? $suite->getName() : '',
                '{type}'  => $this->getDefinitionType($definition),
                '{regex}' => $pattern,
            ]
        );

        return $lines;
    }

    /**
     * Extracts the formatted description from the definition.
     *
     * @return string[]
     */
    private function extractDescription(?Suite $suite, Definition $definition): array
    {
        $lines = [];
        if ($description = $definition->getDescription()) {
            $indent = $suite ? '{space}<def_dimmed>|</def_dimmed> ' : '';
            foreach (explode("\n", $description) as $descriptionLine) {
                $lines[] = strtr(
                    $indent . '{description}',
                    [
                        '{space}'       => $suite ? str_pad('', mb_strlen($suite->getName(), 'utf8') + 1) : '',
                        '{description}' => $descriptionLine,
                    ]
                );
            }
        }

        return $lines;
    }

    /**
     * Extracts the formatted footer from the definition.
     *
     * @return string[]
     */
    private function extractFooter(?Suite $suite, Definition $definition): array
    {
        $lines = [];
        $indent = $suite ? '{space}<def_dimmed>|</def_dimmed> at ' : '';
        $lines[] = strtr(
            $indent . '`{path}`',
            [
                '{space}' => $suite ? str_pad('', mb_strlen($suite->getName(), 'utf8') + 1) : '',
                '{path}'  => $definition->getPath(),
            ]
        );

        if ($this->isVerbose()) {
            $indent = $suite ? '{space}<def_dimmed>|</def_dimmed> on ' : '';
            $lines[] = strtr(
                $indent . '`{filepath}[{start}:{end}]`',
                [
                    '{space}' => $suite ? str_pad('', mb_strlen($suite->getName(), 'utf8') + 1) : '',
                    '{filepath}' => $definition->getReflection()->getFileName(),
                    '{start}' => $definition->getReflection()->getStartLine(),
                    '{end}' => $definition->getReflection()->getEndLine(),
                ]
            );
        }

        return $lines;
    }
}
