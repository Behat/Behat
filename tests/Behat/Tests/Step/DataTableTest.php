<?php

namespace Behat\Tests\Step;

use Behat\Gherkin\Exception\NodeException;
use Behat\Gherkin\Node\TableNode;
use Behat\Step\DataTable;
use PHPUnit\Framework\TestCase;

final class DataTableTest extends TestCase
{
    private DataTable $table;

    protected function setUp(): void
    {
        $this->table = new DataTable(new TableNode([
            10 => ['name', 'colour'],
            11 => ['apple', 'green'],
            12 => ['plum', 'purple'],
        ]));
    }

    public function testAsListsReturnsAllRowsAsLists(): void
    {
        $this->assertSame([
            ['name', 'colour'],
            ['apple', 'green'],
            ['plum', 'purple'],
        ], $this->table->asLists());
    }

    public function testAsMapsUsesTheFirstRowAsKeys(): void
    {
        $this->assertSame([
            ['name' => 'apple', 'colour' => 'green'],
            ['name' => 'plum', 'colour' => 'purple'],
        ], $this->table->asMaps());
    }

    public function testAsMapMapsTheFirstColumnToTheSecondOne(): void
    {
        $this->assertSame([
            'name' => 'colour',
            'apple' => 'green',
            'plum' => 'purple',
        ], $this->table->asMap());
    }

    public function testAsMapsIsEmptyWhenTheTableOnlyHasItsHeaderRow(): void
    {
        $table = new DataTable(new TableNode([['name', 'colour']]));

        $this->assertSame([], $table->asMaps());
    }

    public function testAsMapOnASingleColumnTableMapsEveryRowToAnEmptyList(): void
    {
        $table = new DataTable(new TableNode([['name'], ['apple']]));

        $this->assertSame(['name' => [], 'apple' => []], $table->asMap());
    }

    public function testAsMapOnAWiderTableMapsTheFirstCellToTheRemainingOnes(): void
    {
        $table = new DataTable(new TableNode([
            ['name', 'colour', 'size'],
            ['apple', 'green', 'small'],
        ]));

        $this->assertSame([
            'name' => ['colour', 'size'],
            'apple' => ['green', 'small'],
        ], $table->asMap());
    }

    public function testAccessingARowThatDoesNotExistThrows(): void
    {
        $this->expectException(NodeException::class);
        $this->expectExceptionMessage('Rows #9 does not exist in table.');

        $this->table->row(9);
    }

    public function testAccessingAColumnThatDoesNotExistThrows(): void
    {
        $this->expectException(NodeException::class);
        $this->expectExceptionMessage('Column #9 does not exist in table.');

        $this->table->column(9);
    }

    public function testAccessingACellOutsideTheRowsThrows(): void
    {
        $this->expectException(NodeException::class);
        $this->expectExceptionMessage('Rows #9 does not exist in table.');

        $this->table->cell(9, 0);
    }

    public function testAccessingACellOutsideTheColumnsThrows(): void
    {
        $this->expectException(NodeException::class);
        $this->expectExceptionMessage('Column #9 does not exist in table.');

        $this->table->cell(0, 9);
    }

    public function testCellIsAccessedByZeroBasedRowAndColumn(): void
    {
        $this->assertSame('name', $this->table->cell(0, 0));
        $this->assertSame('purple', $this->table->cell(2, 1));
    }

    public function testRowIsAccessedByZeroBasedIndex(): void
    {
        $this->assertSame(['apple', 'green'], $this->table->row(1));
    }

    public function testColumnIsAccessedByZeroBasedIndex(): void
    {
        $this->assertSame(['colour', 'green', 'purple'], $this->table->column(1));
    }

    public function testHeightCountsAllRows(): void
    {
        $this->assertSame(3, $this->table->height());
    }

    public function testWidthCountsTheColumns(): void
    {
        $this->assertSame(2, $this->table->width());
    }

    public function testAnEmptyTableHasNoDimensions(): void
    {
        $table = new DataTable(new TableNode([]));

        $this->assertTrue($table->isEmpty());
        $this->assertSame(0, $table->height());
        $this->assertSame(0, $table->width());
        $this->assertSame([], $table->asLists());
    }

    public function testANonEmptyTableIsNotEmpty(): void
    {
        $this->assertFalse($this->table->isEmpty());
    }

    public function testTransposeSwapsRowsAndColumns(): void
    {
        $this->assertSame([
            ['name', 'apple', 'plum'],
            ['colour', 'green', 'purple'],
        ], $this->table->transpose()->asLists());
    }

    public function testItStringifiesAsAPaddedTable(): void
    {
        $this->assertSame(
            "| name  | colour |\n| apple | green  |\n| plum  | purple |",
            (string) $this->table,
        );
    }
}
