<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer\Helper;

use Behat\Gherkin\Node\ExampleNode;
use Behat\Gherkin\Node\ExampleTableNode;
use Behat\Gherkin\Node\OutlineNode;
use OutOfBoundsException;

/**
 * Maps an example back to the examples table it was created from.
 *
 * An outline can have any number of examples tables, but {@see OutlineNode::getExamples()} returns a flat list of
 * the examples from all of them. Formatters that want to render the tables as they appear in the feature file need
 * to know which table each example belongs to, and where it sits within that table.
 *
 * Examples are matched on their line number, which is unique within a feature file.
 */
final class ExampleTableResolver
{
    /**
     * Returns the examples table within $outline that $example was created from.
     *
     * @throws OutOfBoundsException if the example does not belong to any of the outline's examples tables
     */
    public function resolveTable(OutlineNode $outline, ExampleNode $example): ExampleTableNode
    {
        foreach ($outline->getExampleTables() as $table) {
            if (in_array($example->getLine(), $table->getLines(), true)) {
                return $table;
            }
        }

        throw new OutOfBoundsException(sprintf(
            'Example on line %d does not belong to any examples table of outline "%s".',
            $example->getLine(),
            $outline->getTitle() ?? '',
        ));
    }

    /**
     * Returns the number of the row that $example was created from, where row 0 is the table header.
     *
     * @throws OutOfBoundsException if the example does not belong to the given examples table
     */
    public function resolveRowNumber(ExampleTableNode $table, ExampleNode $example): int
    {
        $rowNum = array_search($example->getLine(), $table->getLines(), true);

        if (!is_int($rowNum)) {
            throw new OutOfBoundsException(sprintf(
                'Example on line %d does not belong to the given examples table.',
                $example->getLine(),
            ));
        }

        return $rowNum;
    }
}
