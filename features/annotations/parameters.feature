Feature: Parameters
  In order to support different setups
  As a tester
  I need to be able to configure Behat through environment variable

  Background:
    Given a file named "features/bootstrap/bootstrap.php" with:
      """
      <?php
      require_once 'PHPUnit/Autoload.php';
      require_once 'PHPUnit/Framework/Assert/Functions.php';
      """
    And a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\BehatContext,
          Behat\Behat\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeatureContext extends BehatContext
      {
          private $result;
          private $numbers;

          /**
           * @Given /I have basic calculator/
           */
          public function iHaveBasicCalculator() {
              $this->result  = 0;
              $this->numbers = array();
          }

          /**
           * @Given /I have entered (\d+)/
           */
          public function iHaveEntered($number) {
              $this->numbers[] = intval($number);
          }

          /**
           * @When /I add/
           */
          public function iAdd() {
              $this->result  = array_sum($this->numbers);
              $this->numbers = array();
          }

          /**
           * @When /I sub/
           */
          public function iSub() {
              $this->result  = array_shift($this->numbers);
              $this->result -= array_sum($this->numbers);
              $this->numbers = array();
          }

          /**
           * @Then /The result should be (\d+)/
           */
          public function theResultShouldBe($result) {
              assertEquals($result, $this->result);
          }
      }
      """
    And a file named "features/math.feature" with:
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
    And a file named "behat.yml" with:
      """
      default:
        formatter:
          name: progress
      """

  Scenario:
    When I run "behat --no-ansi"
    Then it should pass with:
      """
      ...............

      3 scenarios (3 passed)
      15 steps (15 passed)
      """

  @unix
  Scenario:
    Given "BEHAT_PARAMS" environment variable is set to:
      """
      formatter[name]=pretty&formatter[parameters][paths]=false&formatter[parameters][time]=false
      """
    When I run "behat --no-ansi -c unexistent"
    Then it should pass with:
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

      3 scenarios (3 passed)
      15 steps (15 passed)
      """

  @unix
  Scenario:
    Given "BEHAT_PARAMS" environment variable is set to:
      """
      formatter[name]=pretty&formatter[parameters][time]=false
      """
    When I run "behat --no-ansi -c unexistent"
    Then it should pass with:
      """
      Feature: Math

        Background:                     # features/math.feature:2
          Given I have basic calculator # FeatureContext::iHaveBasicCalculator()

        Scenario Outline:                    # features/math.feature:5
          Given I have entered <number1>     # FeatureContext::iHaveEntered()
          And I have entered <number2>       # FeatureContext::iHaveEntered()
          When I add                         # FeatureContext::iAdd()
          Then The result should be <result> # FeatureContext::theResultShouldBe()

          Examples:
            | number1 | number2 | result |
            | 10      | 12      | 22     |
            | 5       | 3       | 8      |
            | 5       | 5       | 10     |

      3 scenarios (3 passed)
      15 steps (15 passed)
      """

  @unix
  Scenario: JSON-formatted parameters
    Given "BEHAT_PARAMS" environment variable is set to:
      """
      {"formatter":{"name":"pretty", "parameters":{"time":false}}}
      """
    When I run "behat --no-ansi -c unexistent"
    Then it should pass with:
      """
      Feature: Math

        Background:                     # features/math.feature:2
          Given I have basic calculator # FeatureContext::iHaveBasicCalculator()

        Scenario Outline:                    # features/math.feature:5
          Given I have entered <number1>     # FeatureContext::iHaveEntered()
          And I have entered <number2>       # FeatureContext::iHaveEntered()
          When I add                         # FeatureContext::iAdd()
          Then The result should be <result> # FeatureContext::theResultShouldBe()

          Examples:
            | number1 | number2 | result |
            | 10      | 12      | 22     |
            | 5       | 3       | 8      |
            | 5       | 5       | 10     |

      3 scenarios (3 passed)
      15 steps (15 passed)
      """
