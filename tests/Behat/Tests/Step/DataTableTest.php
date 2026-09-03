<?php

namespace Behat\Tests\Step;

use Behat\Gherkin\Node\TableNode;
use Behat\Step\DataTable;
use InvalidArgumentException;
use OutOfBoundsException;
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

    /**
     * @return array<string, array{array<int, list<string>>, list<array<string, string>>}>
     */
    public static function providerAsMaps(): array
    {
        return [
            'header row and value rows' => [
                [['name', 'colour'], ['apple', 'green']],
                [['name' => 'apple', 'colour' => 'green']],
            ],
            'header row only' => [
                [['name', 'colour']],
                [],
            ],
            'empty table' => [
                [],
                [],
            ],
        ];
    }

    /**
     * @dataProvider providerAsMaps
     *
     * @param array<int, list<string>> $rows
     * @param list<array<string, string>> $expect
     */
    public function testAsMapsUsesTheFirstRowAsKeys(array $rows, array $expect): void
    {
        $this->assertSame($expect, (new DataTable(new TableNode($rows)))->asMaps());
    }

    /**
     * @return array<string, array{array<int, list<string>>, array<string, string|null>}>
     */
    public static function providerAsMap(): array
    {
        return [
            'two columns' => [
                [['name', 'colour'], ['apple', 'green'], ['plum', 'purple']],
                ['name' => 'colour', 'apple' => 'green', 'plum' => 'purple'],
            ],
            'single column' => [
                [['name'], ['apple']],
                ['name' => null, 'apple' => null],
            ],
            'empty table' => [
                [],
                [],
            ],
        ];
    }

    /**
     * @dataProvider providerAsMap
     *
     * @param array<int, list<string>> $rows
     * @param array<string, string|null> $expect
     */
    public function testAsMapMapsTheFirstColumnToTheSecondOne(array $rows, array $expect): void
    {
        $this->assertSame($expect, (new DataTable(new TableNode($rows)))->asMap());
    }

    public function testAsMapRejectsATableWithMoreThanTwoColumns(): void
    {
        $table = new DataTable(new TableNode([
            ['name', 'colour', 'size'],
            ['apple', 'green', 'small'],
        ]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A table with 3 columns cannot be converted to a map, it must have at most two columns.');

        $table->asMap();
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

    /**
     * @return array<string, array{callable(DataTable): mixed, string}>
     */
    public static function providerOutOfBoundsAccess(): array
    {
        return [
            'row just past the last one' => [
                static fn (DataTable $table) => $table->row(3),
                'Row #3 does not exist in this table, which has 3 rows.',
            ],
            'negative row' => [
                static fn (DataTable $table) => $table->row(-1),
                'Row #-1 does not exist in this table, which has 3 rows.',
            ],
            'column just past the last one' => [
                static fn (DataTable $table) => $table->column(2),
                'Column #2 does not exist in this table, which has 2 columns.',
            ],
            'negative column' => [
                static fn (DataTable $table) => $table->column(-1),
                'Column #-1 does not exist in this table, which has 2 columns.',
            ],
            'cell in a row just past the last one' => [
                static fn (DataTable $table) => $table->cell(3, 0),
                'Row #3 does not exist in this table, which has 3 rows.',
            ],
            'cell in a column just past the last one' => [
                static fn (DataTable $table) => $table->cell(0, 2),
                'Column #2 does not exist in this table, which has 2 columns.',
            ],
        ];
    }

    /**
     * @dataProvider providerOutOfBoundsAccess
     *
     * @param callable(DataTable): mixed $access
     */
    public function testAccessingARowColumnOrCellThatDoesNotExistThrows(callable $access, string $expect): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage($expect);

        $access($this->table);
    }

    public function testHeightCountsAllRows(): void
    {
        $this->assertSame(3, $this->table->height());
    }

    public function testWidthCountsTheColumns(): void
    {
        $this->assertSame(2, $this->table->width());
    }

    public function testANonEmptyTableIsNotEmpty(): void
    {
        $this->assertFalse($this->table->isEmpty());
    }

    public function testAnEmptyTableHasNoDimensions(): void
    {
        $table = new DataTable(new TableNode([]));

        $this->assertTrue($table->isEmpty());
        $this->assertSame(0, $table->height());
        $this->assertSame(0, $table->width());
        $this->assertSame([], $table->asLists());
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
