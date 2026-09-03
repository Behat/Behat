<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Step;

use Behat\Gherkin\Node\TableNode;
use InvalidArgumentException;
use OutOfBoundsException;
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
     * Every row of a single-column table maps to null.
     *
     * @return array<string, string|null>
     *
     * @throws InvalidArgumentException when the table has more than two columns
     */
    public function asMap(): array
    {
        $width = $this->width();

        if ($width > 2) {
            throw new InvalidArgumentException(sprintf(
                'A table with %d columns cannot be converted to a map, it must have at most two columns.',
                $width,
            ));
        }

        $map = [];

        foreach ($this->asLists() as $row) {
            $map[$row[0]] = $row[1] ?? null;
        }

        return $map;
    }

    /**
     * Returns the value of a single cell by zero-based row and column indexes.
     *
     * @throws OutOfBoundsException when the row or the column does not exist
     */
    public function cell(int $row, int $column): string
    {
        $cells = $this->row($row);

        if (!array_key_exists($column, $cells)) {
            throw new OutOfBoundsException($this->columnIsOutOfBounds($column));
        }

        return $cells[$column];
    }

    /**
     * Returns a row as a list of cell values, by zero-based index.
     *
     * @return list<string>
     *
     * @throws OutOfBoundsException when the row does not exist
     */
    public function row(int $index): array
    {
        $rows = $this->asLists();

        if (!array_key_exists($index, $rows)) {
            throw new OutOfBoundsException(sprintf(
                'Row #%d does not exist in this table, which has %d rows.',
                $index,
                count($rows),
            ));
        }

        return $rows[$index];
    }

    /**
     * Returns a column as a list of cell values, by zero-based index.
     *
     * @return list<string>
     *
     * @throws OutOfBoundsException when the column does not exist
     */
    public function column(int $index): array
    {
        if ($index < 0 || $index >= $this->width()) {
            throw new OutOfBoundsException($this->columnIsOutOfBounds($index));
        }

        return array_column($this->asLists(), $index);
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

    private function columnIsOutOfBounds(int $index): string
    {
        return sprintf(
            'Column #%d does not exist in this table, which has %d columns.',
            $index,
            $this->width(),
        );
    }
}
