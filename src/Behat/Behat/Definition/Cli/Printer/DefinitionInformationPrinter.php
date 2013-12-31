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
use Behat\Testwork\Suite\Suite;

/**
 * Behat definition information printer.
 *
 * Prints definitions with full information about them.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class DefinitionInformationPrinter extends AbstractDefinitionPrinter
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
     * Prints definition.
     *
     * @param Suite        $suite
     * @param Definition[] $definitions
     */
    public function printDefinitions(Suite $suite, $definitions)
    {
        $search = $this->searchCriterion;
        $output = array();

        foreach ($definitions as $definition) {
            $lines = array();
            $regex = $this->getDefinitionPattern($suite, $definition);

            if (null !== $search && false === mb_strpos($regex, $search, 0, 'utf8')) {
                continue;
            }

            $lines[] = strtr(
                '{suite} {+def_dimmed}|{-def_dimmed} {+info}{type}{-info} {+def_regex}{regex}{-def_regex}', array(
                    '{suite}' => $suite->getName(),
                    '{type}'  => $definition->getType(),
                    '{regex}' => $regex,
                )
            );

            if ($definition->getDescription()) {
                $lines[] = strtr(
                    '{space}{+def_dimmed}|{-def_dimmed} {description}', array(
                        '{space}'       => str_pad('', mb_strlen($suite->getName(), 'utf8') + 1),
                        '{description}' => $definition->getDescription()
                    )
                );
            }

            $lines[] = strtr(
                '{space}{+def_dimmed}|{-def_dimmed} at `{path}`', array(
                    '{space}' => str_pad('', mb_strlen($suite->getName(), 'utf8') + 1),
                    '{path}'  => $definition->getPath()
                )
            );

            $output[] = implode(PHP_EOL, $lines) . PHP_EOL;
        }

        $this->write(rtrim(implode(PHP_EOL, $output)));
    }
}
