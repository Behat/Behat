Feature: Scenario Outlines
  In order to write complex features
  As a features writer
  I want to write scenario outlines

  Background:
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context,
          Behat\Behat\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeatureContext implements Context
      {
          private $result;
          private $numbers;

          /**
           * @Given /^I have basic calculator$/
           */
           public function iHaveBasicCalculator() {
              $this->result = 0;
              $this->numbers = array();
           }

           /**
            * @Given /^I have entered (\d+)$/
            */
           public function iHaveEntered($number) {
              $this->numbers[] = intval($number);
           }

           /**
            * @When /^I add$/
            */
           public function iAdd() {
               foreach ($this->numbers as $number) {
                   $this->result += $number;
               }
               $this->numbers = array();
           }

           /**
            * @When /^I sub$/
            */
           public function iSub() {
               $this->result = array_shift($this->numbers);
               foreach ($this->numbers as $number) {
                   $this->result -= $number;
               }
               $this->numbers = array();
           }

           /**
            * @When /^I multiply$/
            */
           public function iMultiply() {
               $this->result = array_shift($this->numbers);
               foreach ($this->numbers as $number) {
                   $this->result *= $number;
               }
               $this->numbers = array();
           }

           /**
            * @When /^I div$/
            */
           public function iDiv() {
               $this->result = array_shift($this->numbers);
               foreach ($this->numbers as $number) {
                   $this->result /= $number;
               }
               $this->numbers = array();
           }

           /**
            * @Then /^The result should be (\d+)$/
            */
           public function theResultShouldBe($result) {
              PHPUnit_Framework_Assert::assertEquals(intval($result), $this->result);
           }
      }
      """

  Scenario: Basic scenario outline
    Given a file named "features/math.feature" with:
      """
      Feature: Math
        Background:
          Given I have basic calculator

        Scenario Outline:
          Given I have entered <number1>
          And I have entered <number2>
          When I add
          Then The result should be <result>

          Examples:
            | number1 | number2 | result |
            | 10      | 12      | 22     |
            | 5       | 3       | 8      |
            | 5       | 5       | 10     |
      """
    When I run "behat --no-colors -f progress features/math.feature"
    Then it should pass with:
      """
      ...............

      3 scenarios (3 passed)
      15 steps (15 passed)
      """

  Scenario: Multiple scenario outlines
    Given a file named "features/math.feature" with:
      """
      Feature: Math
        Background:
          Given I have basic calculator

        Scenario Outline:
          Given I have entered <number1>
          And I have entered <number2>
          When I multiply
          Then The result should be <result>

          Examples:
            | number1 | number2 | result |
            | 10      | 12      | 120    |
            | 5       | 3       | 15     |

        Scenario:
          Given I have entered 10
          And I have entered 3
          When I sub
          Then The result should be 7

        Scenario Outline:
          Given I have entered <number1>
          And I have entered <number2>
          When I div
          Then The result should be <result>

          Examples:
            | number1 | number2 | result |
            | 10      | 2       | 5      |
            | 50      | 5       | 10     |
      """
    When I run "behat --no-colors -f progress features/math.feature"
    Then it should pass with:
      """
      .........................

      5 scenarios (5 passed)
      25 steps (25 passed)
      """

  Scenario: Multiple scenario outlines with failing steps
    Given a file named "features/math.feature" with:
      """
      Feature: Math
        Background:
          Given I have basic calculator

        Scenario Outline:
          Given I have entered <number1>
          And I have entered <number2>
          When I multiply
          Then The result should be <result>

          Examples:
            | number1 | number2 | result |
            | 10      | 12      | 120    |
            | 5       | 4       | 15     |

        Scenario:
          Given I have entered 10
          And I have entered 4
          When I sub
          Then The result should be 7

        Scenario Outline:
          Given I have entered <number1>
          And I have entered <number2>
          When I div
          Then The result should be <result>

          Examples:
            | number1 | number2 | result |
            | 10      | 2       | 5      |
            | 50      | 10      | 2      |
            | 50      | 10      | 4      |
      """
    When I run "behat --no-colors -f progress features/math.feature"
    Then it should fail with:
      """
      .........F....F.........F....F

      --- Failed steps:

          Then The result should be 15 # features/math.feature:9
            Failed asserting that 20 matches expected 15.

          Then The result should be 7 # features/math.feature:20
            Failed asserting that 6 matches expected 7.

          Then The result should be 2 # features/math.feature:26
            Failed asserting that 5 matches expected 2.

          Then The result should be 4 # features/math.feature:26
            Failed asserting that 5 matches expected 4.

      6 scenarios (2 passed, 4 failed)
      30 steps (26 passed, 4 failed)
      """
