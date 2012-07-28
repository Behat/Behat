Feature: Profiles
  In order to test my features
  As a tester
  I need to be able to create and run custom profiles

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
      pretty:
        formatter:
          name: pretty
      """
    And a file named "behat.yml" with:
      """
      default:
        formatter:
          name: progress
      pretty_without_paths:
        formatter:
          name: pretty
          parameters:
            paths: false
      imports:
        - pretty.yml
      """

  Scenario:
    Given I run "behat --no-ansi features/math.feature"
    Then it should pass with:
      """
      ...............

      3 scenarios (3 passed)
      15 steps (15 passed)
      """

  Scenario:
    Given I run "behat --no-ansi --profile pretty_without_paths"
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
    Given I run "behat --no-ansi --profile pretty"
    Then it should pass with:
      """
      Feature: Math

        Background:                     # features/math.feature:2
          Given I have basic calculator # features/steps/math.php:2

        Scenario Outline:                    # features/math.feature:5
          Given I have entered <number1>     # features/steps/math.php:6
          And I have entered <number2>       # features/steps/math.php:6
          When I add                         # features/steps/math.php:9
          Then The result should be <result> # features/steps/math.php:18

          Examples:
            | number1 | number2 | result |
            | 10      | 12      | 22     |
            | 5       | 3       | 8      |
            | 5       | 5       | 10     |

      3 scenarios (3 passed)
      15 steps (15 passed)
      """
