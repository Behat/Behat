Feature: Scenario Outlines
  In order to write complex features
  As a features writer
  I want to write scenario outlines

  Background:
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\ClosuredContextInterface,
          Behat\Behat\Context\BehatContext,
          Behat\Behat\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;
      use Symfony\Component\Finder\Finder;

      if (file_exists(__DIR__ . '/../support/bootstrap.php')) {
          require_once __DIR__ . '/../support/bootstrap.php';
      }

      class FeatureContext extends BehatContext implements ClosuredContextInterface
      {
          public $parameters = array();

          public function __construct(array $parameters) {
              $this->parameters = $parameters;

              if (file_exists(__DIR__ . '/../support/env.php')) {
                  $world = $this;
                  require(__DIR__ . '/../support/env.php');
              }
          }

          public function getStepDefinitionResources() {
              if (file_exists(__DIR__ . '/../steps')) {
                  $finder = new Finder();
                  return $finder->files()->name('*.php')->in(__DIR__ . '/../steps');
              }
              return array();
          }

          public function getHookDefinitionResources() {
              if (file_exists(__DIR__ . '/../support/hooks.php')) {
                  return array(__DIR__ . '/../support/hooks.php');
              }
              return array();
          }

          public function __call($name, array $args) {
              if (isset($this->$name) && is_callable($this->$name)) {
                  return call_user_func_array($this->$name, $args);
              } else {
                  $trace = debug_backtrace();
                  trigger_error(
                      'Call to undefined method ' . get_class($this) . '::' . $name .
                      ' in ' . $trace[0]['file'] .
                      ' on line ' . $trace[0]['line'],
                      E_USER_ERROR
                  );
              }
          }
      }
      """
    And a file named "features/support/bootstrap.php" with:
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
          foreach ($world->numbers as $number) {
              $world->result += $number;
          }
          $world->numbers = array();
      });
      $steps->When('/^I sub$/', function($world) {
          $world->result = array_shift($world->numbers);
          foreach ($world->numbers as $number) {
              $world->result -= $number;
          }
          $world->numbers = array();
      });
      $steps->When('/^I multiply$/', function($world) {
          $world->result = array_shift($world->numbers);
          foreach ($world->numbers as $number) {
              $world->result *= $number;
          }
          $world->numbers = array();
      });
      $steps->When('/^I div$/', function($world) {
          $world->result = array_shift($world->numbers);
          foreach ($world->numbers as $number) {
              $world->result /= $number;
          }
          $world->numbers = array();
      });
      $steps->Then('/^The result should be (\d+)$/', function($world, $result) {
          assertEquals($result, $world->result);
      });
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
    When I run "behat --no-ansi -f progress features/math.feature"
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
    When I run "behat --no-ansi -f progress features/math.feature"
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
      """
    When I run "behat --no-ansi -f progress features/math.feature"
    Then it should fail with:
      """
      .........F....F.........F

      (::) failed steps (::)

      01. Failed asserting that 20 matches expected '15'.
          In step `Then The result should be 15'. # features/steps/math.php:36
          From scenario ***.                      # features/math.feature:5
          Of feature `Math'.                      # features/math.feature

      02. Failed asserting that 6 matches expected '7'.
          In step `Then The result should be 7'.  # features/steps/math.php:36
          From scenario ***.                      # features/math.feature:16
          Of feature `Math'.                      # features/math.feature

      03. Failed asserting that 5 matches expected '2'.
          In step `Then The result should be 2'.  # features/steps/math.php:36
          From scenario ***.                      # features/math.feature:22
          Of feature `Math'.                      # features/math.feature

      5 scenarios (2 passed, 3 failed)
      25 steps (22 passed, 3 failed)
      """
