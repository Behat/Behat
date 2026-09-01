<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Step;

use Behat\Gherkin\Exception\NodeException;
use Behat\Gherkin\Node\TableNode;
use Stringable;

/**
 * Represents a Gherkin data table passed to a step definition.
 *
 * Use this type instead of {@see TableNode} in step definition parameters to
 * keep them decoupled from the Gherkin AST. The API is modelled on the
 * cucumber DataTable classes.
 *
 * @api
 */
final class DataTable implements Stringable
{
    /**
     * @internal instances are created by Behat, from the Gherkin node of the step
     */
    public function __construct(
        private readonly TableNode $tableNode,
    ) {
    }

    /**
     * Returns the whole table, including the header row if any, as a list of
     * rows, each row being a list of cell values.
     *
     * @return list<list<string>>
     */
    public function asLists(): array
    {
        return array_values(array_map(array_values(...), $this->tableNode->getTable()));
    }

    /**
     * Returns the table as a list of associative arrays, using the first row
     * as keys for the values of every following row.
     *
     * @return list<array<string, string>>
     */
    public function asMaps(): array
    {
        return $this->tableNode->getHash();
    }

    /**
     * Returns a two-column table as an associative array, mapping the cells
     * of the first column to the cells of the second column.
     *
     * A row that has more than two cells maps to the list of its remaining
     * cells, and a single-column row maps to an empty list.
     *
     * @return array<string, string|list<string>>
     */
    public function asMap(): array
    {
        return $this->tableNode->getRowsHash();
    }

    /**
     * Returns the value of a single cell by zero-based row and column indexes.
     *
     * @throws NodeException when the row or the column does not exist
     */
    public function cell(int $row, int $column): string
    {
        $cells = $this->row($row);

        if (!array_key_exists($column, $cells)) {
            throw new NodeException(sprintf('Column #%s does not exist in table.', $column));
        }

        return $cells[$column];
    }

    /**
     * Returns a row as a list of cell values, by zero-based index.
     *
     * @return list<string>
     */
    public function row(int $index): array
    {
        return $this->tableNode->getRow($index);
    }

    /**
     * Returns a column as a list of cell values, by zero-based index.
     *
     * @return list<string>
     */
    public function column(int $index): array
    {
        return $this->tableNode->getColumn($index);
    }

    /**
     * Returns the number of rows.
     */
    public function height(): int
    {
        return count($this->tableNode->getRows());
    }

    /**
     * Returns the number of columns.
     */
    public function width(): int
    {
        return count($this->tableNode->getRows()[0] ?? []);
    }

    /**
     * Returns whether the table has no rows at all.
     */
    public function isEmpty(): bool
    {
        return $this->tableNode->getRows() === [];
    }

    /**
     * Returns a new table with rows and columns swapped.
     */
    public function transpose(): self
    {
        $rows = array_values(array_map(array_values(...), $this->tableNode->getTable()));
        $transposed = [];
        foreach ($rows as $rowIndex => $row) {
            foreach ($row as $columnIndex => $cell) {
                $transposed[$columnIndex][$rowIndex] = $cell;
            }
        }

        return new self(new TableNode($transposed));
    }

    public function __toString(): string
    {
        return $this->tableNode->getTableAsString();
    }
}
