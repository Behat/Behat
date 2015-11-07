<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition\Printer;

use Behat\Testwork\Suite\Suite;

/**
 * Prints simple definitions list.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ConsoleDefinitionListPrinter extends ConsoleDefinitionPrinter
{
    /**
     * {@inheritdoc}
     */
    public function printDefinitions(Suite $suite, $definitions)
    {
        $output = array();

        foreach ($definitions as $definition) {
            $definition = $this->translateDefinition($suite, $definition);

            $output[] = strtr(
                '{suite} <def_dimmed>|</def_dimmed> <info>{type}</info> <def_regex>{regex}</def_regex>', array(
                    '{suite}' => $suite->getName(),
                    '{type}'  => $this->getDefinitionType($definition, true),
                    '{regex}' => $definition->getPattern(),
                )
            );
        }

        $this->write(rtrim(implode(PHP_EOL, $output)));
    }
}
