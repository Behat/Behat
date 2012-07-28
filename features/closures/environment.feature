Feature: Environment consistency
  In order to maintain stable behavior tests
  As a feature writer
  I need a separate environment for every scenario/outline

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
    And a file named "features/steps/apple_steps.php" with:
      """
      <?php
      $steps->Given('/^I have (\d+) apples?$/', function($world, $apples) {
          $world->apples = intval($apples);
      });
      $steps->When('/^I ate (\d+) apples?$/', function($world, $apples) {
          $world->apples -= intval($apples);
      });
      $steps->When('/^I found (\d+) apples?$/', function($world, $apples) {
          $world->apples += intval($apples);
      });
      $steps->Then('/^I should have (\d+) apples$/', function($world, $apples) {
          assertEquals(intval($apples), $world->apples);
      });
      """

  Scenario: True "apples story"
    Given a file named "features/apples.feature" with:
      """
      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Background:
          Given I have 3 apples

        Scenario: I'm little hungry
          When I ate 1 apple
          Then I should have 2 apples

        Scenario: Found more apples
          When I found 2 apples
          Then I should have 5 apples

        Scenario Outline: Other situations
          When I ate <ate> apples
          And I found <found> apples
          Then I should have <result> apples

          Examples:
            | ate | found | result |
            | 3   | 1     | 1      |
            | 0   | 5     | 8      |
            | 2   | 2     | 3      |
      """
    When I run "behat --no-ansi -f progress features/apples.feature"
    Then it should pass with:
      """
      ..................

      5 scenarios (5 passed)
      18 steps (18 passed)
      """

  Scenario: False "apples story"
    Given a file named "features/apples.feature" with:
      """
      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Background:
          Given I have 3 apples

        Scenario: I'm little hungry
          When I ate 1 apple
          Then I should have 5 apples

        Scenario: Found more apples
          When I found 10 apples
          Then I should have 10 apples

        Scenario Outline: Other situations
          When I ate <ate> apples
          And I found <found> apples
          Then I should have <result> apples

          Examples:
            | ate | found | result |
            | 3   | 1     | 3      |
            | 0   | 5     | 8      |
            | 2   | 2     | 4      |
      """
    When I run "behat --no-ansi -f progress features/apples.feature"
    Then it should fail with:
      """
      ..F..F...F.......F

      (::) failed steps (::)

      01. Failed asserting that 2 matches expected 5.
          In step `Then I should have 5 apples'. # features/steps/apple_steps.php:11
          From scenario `I'm little hungry'.     # features/apples.feature:9
          Of feature `Apples story'.             # features/apples.feature

      02. Failed asserting that 13 matches expected 10.
          In step `Then I should have 10 apples'. # features/steps/apple_steps.php:11
          From scenario `Found more apples'.      # features/apples.feature:13
          Of feature `Apples story'.              # features/apples.feature

      03. Failed asserting that 1 matches expected 3.
          In step `Then I should have 3 apples'.  # features/steps/apple_steps.php:11
          From scenario `Other situations'.       # features/apples.feature:17
          Of feature `Apples story'.              # features/apples.feature

      04. Failed asserting that 3 matches expected 4.
          In step `Then I should have 4 apples'.  # features/steps/apple_steps.php:11
          From scenario `Other situations'.       # features/apples.feature:17
          Of feature `Apples story'.              # features/apples.feature

      5 scenarios (1 passed, 4 failed)
      18 steps (14 passed, 4 failed)
      """

  Scenario: Environment parameters
    Given a file named "behat.yml" with:
      """
      default:
        context:
          parameters:
            parameter1: val_one
            parameter2:
              everzet: behat_admin
              avalanche123: behat_admin
      """
    And a file named "features/steps/env_vars_steps.php" with:
      """
      <?php
      $steps->Then('/^environment parameter "([^"]*)" should be equal to "([^"]*)"$/', function($world, $key, $val) {
          assertEquals($val, $world->parameters[$key]);
      });

      $steps->And('/^environment parameter "([^"]*)" should be array with (\d+) elements$/', function($world, $key, $count) {
          assertInternalType('array', $world->parameters[$key]);
          assertEquals(2, count($world->parameters[$key]));
      });
      """
    And a file named "features/params.feature" with:
      """
      Feature: Environment parameters
        In order to run a browser
        As feature runner
        I need to be able to configure behat environment

        Scenario: I'm little hungry
          Then environment parameter "parameter1" should be equal to "val_one"
          And environment parameter "parameter2" should be array with 2 elements
      """
  When I run "behat --no-ansi -f progress features/params.feature"
  Then it should pass with:
    """
    ..

    1 scenario (1 passed)
    2 steps (2 passed)
    """
