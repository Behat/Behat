Feature: Profiles
  In order to test my features
  As a tester
  I need to be able to create and run custom profiles

  Background:
    Given a file named "features/support/bootstrap.php" with:
      """
      <?php
      require_once 'PHPUnit/Autoload.php';
      require_once 'PHPUnit/Framework/Assert/Functions.php';
      """
    And a file named "features/steps/math.php" with:
      """
      <?php
      $steps->Given('/^I have basic calculator$/', function($world) {
          $world->result  = 0;
          $world->numbers = array();
      });
      $steps->Given('/^I have entered (\d+)$/', function($world, $number) {
          $world->numbers[] = intval($number);
      });
      $steps->When('/^I add$/', function($world) {
          $world->result  = array_sum($world->numbers);
          $world->numbers = array();
      });
      $steps->When('/^I sub$/', function($world) {
          $world->result  = array_shift($world->numbers);
          $world->result -= array_sum($world->numbers);
          $world->numbers = array();
      });
      $steps->Then('/^The result should be (\d+)$/', function($world, $result) {
          assertEquals($result, $world->result);
      });
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
      formatter:
        name: pretty
      """
    And a file named "progress.yml" with:
      """
      formatter:
        name: progress
      """
    And a file named "behat.yml" with:
      """
      formatter:
        name: progress
      """

  Scenario:
    Given I run "behat -TC features/math.feature"
    Then it should pass with:
      """
      ...............
      
      3 scenarios (3 passed)
      15 steps (15 passed)
      """

  Scenario:
    Given I run "behat -TCc progress.yml"
    Then it should pass with:
      """
      ...............
      
      3 scenarios (3 passed)
      15 steps (15 passed)
      """

  Scenario:
    Given I run "behat -TCc pretty.yml"
    Then it should pass with:
      """
      Feature: Math
      
        Background:                     # features/math.feature:2
          Given I have basic calculator # features/steps/math.php:5
      
        Scenario Outline:                    # features/math.feature:5
          Given I have entered <number1>     # features/steps/math.php:8
          And I have entered <number2>       # features/steps/math.php:8
          When I add                         # features/steps/math.php:12
          Then The result should be <result> # features/steps/math.php:20
      
          Examples:
            | number1 | number2 | result |
            | 10      | 12      | 22     |
            | 5       | 3       | 8      |
            | 5       | 5       | 10     |
      
      3 scenarios (3 passed)
      15 steps (15 passed)
      """
