Feature: Step Arguments
  In order to write extended steps
  As a feature writer
  I need an ability to specify Table & PyString arguments to steps

  Background:
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;
      use Behat\Step\Given;
      use Behat\Step\Then;

      class FeatureContext implements Context
      {
          private $input;
          private $strings = array();
          private $tables = array();

          public function __construct() {
              $this->strings[1] = "hello,\n  w\n   o\nr\nl\n   d";
              $this->tables[1]  = array(
                array('item1' => 'super', 'item2' => 'mega', 'item3' => 'extra'),
                array('item1' => 'hyper', 'item2' => 'mini', 'item3' => 'XXL'),
              );
          }

          #[Given('/^a pystring:$/')]
          public function aPystring(PyStringNode $string) {
              $this->input = $string;
          }

          #[Given('/^an untyped pystring:$/')]
          public function anUntypedPystring($string) {
              $this->input = $string;
          }

          #[Given('/^a table:$/')]
          public function aTable(TableNode $table) {
              $this->input = $table;
          }

          #[Given('/^an untyped table:$/')]
          public function anUntypedTable($table) {
              $this->input = $table;
          }

          #[Then('/^it must be equals to string (\d+)$/')]
          public function itMustBeEqualsToString($number) {
              \PHPUnit\Framework\Assert::assertEquals($this->strings[intval($number)], (string) $this->input);
          }

          #[Then('/^it must be equals to table (\d+)$/')]
          public function itMustBeEqualsToTable($number) {
              \PHPUnit\Framework\Assert::assertEquals($this->tables[intval($number)], $this->input->getHash());
          }

          #[Given('/^I have number2 = (?P<number2>\d+) and number1 = (?P<number1>\d+)$/')]
          public function iHaveNumberAndNumber($number1, $number2) {
              \PHPUnit\Framework\Assert::assertEquals(13, intval($number1));
              \PHPUnit\Framework\Assert::assertEquals(243, intval($number2));
          }

          #[Given('/^a step with no argument$/')]
          public function aStepWithNoArgument(): void {
          }
      }
      """

  Scenario: PyStrings
    Given a file named "features/pystring.feature" with:
      """
      Feature: PyStrings
        Scenario:
          Given a pystring:
            '''
            hello,
              w
               o
          r
           l
               d
            '''
          Then it must be equals to string 1
      """
    When I run "behat --no-colors -f progress features/pystring.feature"
    Then it should pass with:
      """
      ..

      1 scenario (1 passed)
      2 steps (2 passed)
      """

  Scenario: PyString tokens
    Given a file named "features/pystring_tokens.feature" with:
      """
      Feature: PyStrings
        Scenario Outline:
          Given a pystring:
            '''
            <word1>
              w
               o
          r
           <word2>
               d
            '''
          Then it must be equals to string 1

          Examples:
            | word1  | word2 |
            | hello, | l     |
      """
    When I run "behat --no-colors -f progress features/pystring_tokens.feature"
    Then it should pass with:
      """
      ..

      1 scenario (1 passed)
      2 steps (2 passed)
      """

  Scenario: Table tokens
    Given a file named "features/table_tokens.feature" with:
      """
      Feature: Tables
        Scenario Outline:
          Given a table:
            | item1   | item2   | item3   |
            | <word1> | <word3> | extra   |
            | hyper   | mini    | <word2> |
          Then it must be equals to table 1

          Examples:
            | word1 | word2 | word3 |
            | super | XXL   | mega  |
      """
    When I run "behat --no-colors -f progress features/table_tokens.feature"
    Then it should pass with:
      """
      ..

      1 scenario (1 passed)
      2 steps (2 passed)
      """

  Scenario: Table
    Given a file named "features/table.feature" with:
      """
      Feature: Tables
        Scenario:
          Given a table:
            | item1 | item2 | item3 |
            | super | mega  | extra |
            | hyper | mini  | XXL   |
          Then it must be equals to table 1
      """
    When I run "behat --no-colors -f progress features/table.feature"
    Then it should pass with:
      """
      ..

      1 scenario (1 passed)
      2 steps (2 passed)
      """

  Scenario: given TableNode argument that is not defined in context
    Given a file named "features/known-argument-exception.feature" with:
      """
      Feature: Tables
        Scenario:
          Given a step with no argument
            | item1 | item2 | item3 |
            | super | mega  | extra |
            | hyper | mini  | XXL   |
      """
    When I run "behat --no-colors -f progress features/known-argument-exception.feature"
    Then it should fail with:
      """
      You have passed a TableNode or PystringNode as an argument, but it was not used in the function. This is probably an error in your feature file.
      """
      
    Given a file named "features/known-argument-exception.feature" with:
      """
      Feature: Tables
        Scenario:
          Given an untyped table:
            | item1 | item2 | item3 |
            | super | mega  | extra |
            | hyper | mini  | XXL   |
      """
    When I run "behat --no-colors -f progress features/known-argument-exception.feature"
    Then it should pass

  Scenario: given PyStringNode argument that is not defined in context
    Given a file named "features/known-argument-exception.feature" with:
      """
      Feature: PystringNodes
        Scenario:
          Given a step with no argument
            '''
            <word1>
              w
               o
          r
           <word2>
               d
            '''
      """
    When I run "behat --no-colors -f progress features/known-argument-exception.feature"
    Then it should fail with:
      """
      You have passed a TableNode or PystringNode as an argument, but it was not used in the function. This is probably an error in your feature file.
      """
      
    Given a file named "features/known-argument-exception.feature" with:
      """
      Feature: PystringNodes
        Scenario:
          Given an untyped pystring:
            '''
            <word1>
              w
               o
          r
           <word2>
               d
            '''
      """
    When I run "behat --no-colors -f progress features/known-argument-exception.feature"
    Then it should pass

  Scenario: Named arguments
    Given a file named "features/named_args.feature" with:
      """
      Feature: Named arguments
        In order to maintain i18n for steps
        As a step developer
        I need to be able to declare regex with named parameters

        Scenario:
          Given I have number2 = 243 and number1 = 13
      """
    When I run "behat --no-colors -f progress features/named_args.feature "
    Then it should pass with:
      """
      .

      1 scenario (1 passed)
      1 step (1 passed)
      """
