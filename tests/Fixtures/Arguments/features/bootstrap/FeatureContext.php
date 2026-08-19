<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Step\DataTable;
use Behat\Step\DocString;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Tests\Fixtures\Assert;

class FeatureContext implements Context
{
    private $input;
    private $strings = [];
    private $tables = [];

    public function __construct()
    {
        $this->strings[1] = "hello,\n  w\n   o\nr\nl\n   d";
        $this->tables[1] = [
            ['item1' => 'super', 'item2' => 'mega', 'item3' => 'extra'],
            ['item1' => 'hyper', 'item2' => 'mini', 'item3' => 'XXL'],
        ];
    }

    #[Given('/^a pystring:$/')]
    public function aPystring(PyStringNode $string)
    {
        $this->input = $string;
    }

    #[Given('/^an untyped pystring:$/')]
    public function anUntypedPystring($string)
    {
        $this->input = $string;
    }

    #[Given('/^a table:$/')]
    public function aTable(TableNode $table)
    {
        $this->input = $table;
    }

    #[Given('/^an untyped table:$/')]
    public function anUntypedTable($table)
    {
        $this->input = $table;
    }

    #[Then('/^it must be equals to string (\d+)$/')]
    public function itMustBeEqualsToString($number)
    {
        Assert::assertEquals($this->strings[intval($number)], (string) $this->input);
    }

    #[Then('/^it must be equals to table (\d+)$/')]
    public function itMustBeEqualsToTable($number)
    {
        Assert::assertEquals($this->tables[intval($number)], $this->input->getHash());
    }

    #[Given('/^I have number2 = (?P<number2>\d+) and number1 = (?P<number1>\d+)$/')]
    public function iHaveNumberAndNumber($number1, $number2)
    {
        Assert::assertEquals(13, intval($number1));
        Assert::assertEquals(243, intval($number2));
    }

    #[Given('/^a step with no argument$/')]
    public function aStepWithNoArgument(): void
    {
    }

    #[Given('/^a data table:$/')]
    public function aDataTable(DataTable $table)
    {
        $this->input = $table;
    }

    #[Then('/^the data table must be equals to table (\d+)$/')]
    public function theDataTableMustBeEqualsToTable($number)
    {
        PHPUnit\Framework\Assert::assertInstanceOf(DataTable::class, $this->input);
        PHPUnit\Framework\Assert::assertEquals($this->tables[intval($number)], $this->input->asMaps());
    }

    #[Given('/^a doc string:$/')]
    public function aDocString(DocString $string)
    {
        $this->input = $string;
    }

    #[Then('/^the doc string must be equals to string (\d+)$/')]
    public function theDocStringMustBeEqualsToString($number)
    {
        PHPUnit\Framework\Assert::assertInstanceOf(DocString::class, $this->input);
        PHPUnit\Framework\Assert::assertEquals($this->strings[intval($number)], $this->input->getContent());
    }

    #[Given('/^a table that could be wrapped or not:$/')]
    public function aTableThatCouldBeWrappedOrNot(TableNode|DataTable $table)
    {
        $this->input = $table;
    }

    #[Then('/^the argument must be a raw TableNode$/')]
    public function theArgumentMustBeARawTableNode()
    {
        PHPUnit\Framework\Assert::assertInstanceOf(TableNode::class, $this->input);
    }
}
