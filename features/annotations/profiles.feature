Feature: Profiles
  In order to test my features
  As a tester
  I need to be able to create and run custom profiles

  Background:
    Given a file named "features/bootstrap/bootstrap.php" with:
      """
      <?php
      require_once 'PHPUnit/Autoload.php';
      require_once 'PHPUnit/Framework/Assert/Functions.php';
      """
    And a file named "features/bootstrap/FeaturesContext.php" with:
      """
      <?php

      use Behat\Behat\Context\BehatContext, Behat\Behat\Exception\Pending;
      use Behat\Gherkin\Node\PyStringNode,  Behat\Gherkin\Node\TableNode;

      class FeaturesContext extends BehatContext implements Behat\Behat\Context\AnnotatedContextInterface
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
    And a file named "pretty.yml" with:
      """
      pretty:
        formatter:
          name: pretty
      """
    And a file named "behat.yml" with:
      """
      default:
        formatter:
          name: progress
      progress:
        formatter:
          name: progress
          parameters:
            decorated: true
      imports:
        - pretty.yml
      """

  Scenario:
    Given I run "behat features/math.feature"
    Then it should pass with:
      """
      ...............
      
      3 scenarios (3 passed)
      15 steps (15 passed)
      """

  Scenario:
    Given I run "behat --profile progress"
    Then it should pass with:
      """
      [32m.[0m[32m.[0m[32m.[0m[32m.[0m[32m.[0m[32m.[0m[32m.[0m[32m.[0m[32m.[0m[32m.[0m[32m.[0m[32m.[0m[32m.[0m[32m.[0m[32m.[0m
      
      3 scenarios ([32m3 passed[0m)
      15 steps ([32m15 passed[0m)
      """

  Scenario:
    Given I run "behat --profile pretty"
    Then it should pass with:
      """
      Feature: Math
      
        Background:                     # features/math.feature:2
          Given I have basic calculator # FeaturesContext::iHaveBasicCalculator()
      
        Scenario Outline:                    # features/math.feature:5
          Given I have entered <number1>     # FeaturesContext::iHaveEntered()
          And I have entered <number2>       # FeaturesContext::iHaveEntered()
          When I add                         # FeaturesContext::iAdd()
          Then The result should be <result> # FeaturesContext::theResultShouldBe()
      
          Examples:
            | number1 | number2 | result |
            | 10      | 12      | 22     |
            | 5       | 3       | 8      |
            | 5       | 5       | 10     |
      
      3 scenarios (3 passed)
      15 steps (15 passed)
      """
