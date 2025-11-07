<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Step\Given;
use Behat\Step\Then;

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
        PHPUnit\Framework\Assert::assertEquals($this->strings[intval($number)], (string) $this->input);
    }

    #[Then('/^it must be equals to table (\d+)$/')]
    public function itMustBeEqualsToTable($number)
    {
        PHPUnit\Framework\Assert::assertEquals($this->tables[intval($number)], $this->input->getHash());
    }

    #[Given('/^I have number2 = (?P<number2>\d+) and number1 = (?P<number1>\d+)$/')]
    public function iHaveNumberAndNumber($number1, $number2)
    {
        PHPUnit\Framework\Assert::assertEquals(13, intval($number1));
        PHPUnit\Framework\Assert::assertEquals(243, intval($number2));
    }

    #[Given('/^a step with no argument$/')]
    public function aStepWithNoArgument(): void
    {
    }
}
