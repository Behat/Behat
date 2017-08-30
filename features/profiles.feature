Feature: Profiles
  In order to test my features
  As a tester
  I need to be able to create and run custom profiles

  Background:
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
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
              PHPUnit_Framework_Assert::assertEquals($result, $this->result);
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
    And a file named "pretty.yml" with:
      """
      pretty:
        formatters:
          progress: false
          pretty: ~
      """
    And a file named "behat.yml" with:
      """
      default:
        formatters:
          pretty:   false
          progress: ~

      pretty_without_paths:
        formatters:
          progress: false
          pretty:
            paths: false

      imports:
        - pretty.yml
      """

  Scenario:
    Given I run "behat --no-colors features/math.feature"
    Then it should pass with:
      """
      ...............

      3 scenarios (3 passed)
      15 steps (15 passed)
      """

  Scenario:
    Given I run "behat --no-colors --profile pretty_without_paths"
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

  Scenario:
    Given I run "behat --no-colors --profile pretty"
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
