Feature: Pretty Formatter
  In order to debug features
  As a feature writer
  I need to have pretty formatter

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

  Scenario: Complex
    Given a file named "features/steps/math.php" with:
      """
      <?php
      $steps->Given('/I have entered (\d+)/', function($world, $num) {
          assertObjectNotHasAttribute('value', $world);
          $world->value = $num;
      });

      $steps->Then('/I must have (\d+)/', function($world, $num) {
          assertEquals($num, $world->value);
      });

      $steps->When('/I add (\d+)/', function($world, $num) {
          $world->value += $num;
      });

      $steps->Given('/^Something not done yet$/', function($world) {
          throw new \Behat\Behat\Exception\PendingException();
      });
      """
    And a file named "features/World.feature" with:
      """
      Feature: World consistency
        In order to maintain stable behaviors
        As a features developer
        I want, that "World" flushes between scenarios

        Background:
          Given I have entered 10

        Scenario: Undefined
          Then I must have 10
          And Something new
          Then I must have 10

        Scenario: Pending
          Then I must have 10
          And Something not done yet
          Then I must have 10

        Scenario: Failed
          When I add 4
          Then I must have 13

        Scenario Outline: Passed & Failed
          Given I must have 10
          When I add <value>
          Then I must have <result>

          Examples:
            | value | result |
            |  5    | 16     |
            |  10   | 20     |
            |  23   | 32     |
      """
    When I run "behat --no-ansi -f pretty"
    Then it should fail with:
      """
      Feature: World consistency
        In order to maintain stable behaviors
        As a features developer
        I want, that "World" flushes between scenarios

        Background:               # features/World.feature:6
          Given I have entered 10 # features/steps/math.php:2

        Scenario: Undefined       # features/World.feature:9
          Then I must have 10     # features/steps/math.php:7
          And Something new
          Then I must have 10     # features/steps/math.php:7

        Scenario: Pending            # features/World.feature:14
          Then I must have 10        # features/steps/math.php:7
          And Something not done yet # features/steps/math.php:15
            TODO: write pending definition
          Then I must have 10        # features/steps/math.php:7

        Scenario: Failed             # features/World.feature:19
          When I add 4               # features/steps/math.php:11
          Then I must have 13        # features/steps/math.php:7
            Failed asserting that 14 matches expected '13'.

        Scenario Outline: Passed & Failed # features/World.feature:23
          Given I must have 10            # features/steps/math.php:7
          When I add <value>              # features/steps/math.php:11
          Then I must have <result>       # features/steps/math.php:7

          Examples:
            | value | result |
            | 5     | 16     |
              Failed asserting that 15 matches expected '16'.
            | 10    | 20     |
            | 23    | 32     |
              Failed asserting that 33 matches expected '32'.

      6 scenarios (1 passed, 1 pending, 1 undefined, 3 failed)
      23 steps (16 passed, 2 skipped, 1 pending, 1 undefined, 3 failed)

      You can implement step definitions for undefined steps with these snippets:

      $steps->Given('/^Something new$/', function($world) {
          throw new \Behat\Behat\Exception\PendingException();
      });
      """

  Scenario: Multiple parameters
    Given a file named "features/steps/math.php" with:
      """
      <?php
      $steps->Given('/I have entered (\d+)/', function($world, $num) {
          assertObjectNotHasAttribute('value', $world);
          $world->value = $num;
      });

      $steps->Then('/I must have (\d+)/', function($world, $num) {
          assertEquals($num, $world->value);
      });

      $steps->When('/I (add|subtract) the value (\d+)/', function($world, $op, $num) {
          if ($op == 'add')
            $world->value += $num;
          elseif ($op == 'subtract')
            $world->value -= $num;
      });
      """
    And a file named "features/World.feature" with:
      """
      Feature: World consistency
        In order to maintain stable behaviors
        As a features developer
        I want, that "World" flushes between scenarios

        Background:
          Given I have entered 10

        Scenario: Adding
          Then I must have 10
          And I add the value 6
          Then I must have 16

        Scenario: Subtracting
          Then I must have 10
          And I subtract the value 6
          Then I must have 4
      """
    When I run "behat --no-ansi -f pretty --ansi"
    Then it should pass with:
      """
      Feature: World consistency
        In order to maintain stable behaviors
        As a features developer
        I want, that "World" flushes between scenarios

        Background:               [30m# features/World.feature:6[0m
          [32mGiven I have entered [0m[32;1m10[0m[32m[0m [30m# features/steps/math.php:2[0m

        Scenario: Adding          [30m# features/World.feature:9[0m
          [32mThen I must have [0m[32;1m10[0m[32m[0m     [30m# features/steps/math.php:7[0m
          [32mAnd I [0m[32;1madd[0m[32m the value [0m[32;1m6[0m[32m[0m   [30m# features/steps/math.php:11[0m
          [32mThen I must have [0m[32;1m16[0m[32m[0m     [30m# features/steps/math.php:7[0m

        Scenario: Subtracting        [30m# features/World.feature:14[0m
          [32mThen I must have [0m[32;1m10[0m[32m[0m        [30m# features/steps/math.php:7[0m
          [32mAnd I [0m[32;1msubtract[0m[32m the value [0m[32;1m6[0m[32m[0m [30m# features/steps/math.php:11[0m
          [32mThen I must have [0m[32;1m4[0m[32m[0m         [30m# features/steps/math.php:7[0m

      2 scenarios ([32m2 passed[0m)
      8 steps ([32m8 passed[0m)
      """
