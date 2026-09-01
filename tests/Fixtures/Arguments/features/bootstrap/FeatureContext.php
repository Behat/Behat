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
    private $text;
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
        Assert::assertSame(DataTable::class, $this->input::class);
        Assert::assertEquals($this->tables[intval($number)], $this->input->asMaps());
    }

    #[Given('/^a doc string:$/')]
    public function aDocString(DocString $string)
    {
        $this->input = $string;
    }

    #[Then('/^the doc string must be equals to string (\d+)$/')]
    public function theDocStringMustBeEqualsToString($number)
    {
        Assert::assertSame(DocString::class, $this->input::class);
        Assert::assertEquals($this->strings[intval($number)], $this->input->getContent());
    }

    #[Given('/^an untyped step that takes "([^"]*)" and these values:$/')]
    public function anUntypedStepThatTakesAndTheseValues($text, $table)
    {
        $this->input = $table;
        $this->text = $text;
    }

    #[Given('/^an untyped step that takes "([^"]*)" and this text:$/')]
    public function anUntypedStepThatTakesAndThisText($text, $string)
    {
        $this->input = $string;
        $this->text = $text;
    }

    #[Then('/^the argument must be a raw (TableNode|PyStringNode)$/')]
    public function theArgumentMustBeARawGherkinNode($class)
    {
        Assert::assertSame('Behat\\Gherkin\\Node\\' . $class, $this->input::class);
    }

    #[Then('/^the other argument must be "([^"]*)"$/')]
    public function theOtherArgumentMustBe($expected)
    {
        Assert::assertSame($expected, $this->text);
    }
}
