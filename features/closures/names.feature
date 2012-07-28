Feature: Names
  In order to run only needed features
  As a Behat user
  I need to Behat support features & scenario/outline names filtering

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
    And a file named "features/steps/steps.php" with:
      """
      <?php
      $steps->Given('/^Some slow step N(\d+)$/', function($world, $num) {});
      $steps->Given('/^Some normal step N(\d+)$/', function($world, $num) {});
      $steps->Given('/^Some fast step N(\d+)$/', function($world, $num) {});
      """
    And a file named "features/feature1.feature" with:
      """
      Feature: First Feature

        Background:
          Given Some slow step N11

        Scenario: First Scenario
          Given Some slow step N12
          And Some normal step N13

        Scenario: Second Scenario
          Given Some fast step N14
      """
    And a file named "features/feature2.feature" with:
      """
      Feature: Second Feature

        Background:
          Given Some normal step N21

        Scenario: First Scenario
          Given Some slow step N22
          And Some fast step N23
      """

  Scenario: First Name
    When I run "behat --no-ansi -f pretty --name First"
    Then it should pass with:
      """
      Feature: First Feature

        Background:                # features/feature1.feature:3
          Given Some slow step N11 # features/steps/steps.php:2

        Scenario: First Scenario   # features/feature1.feature:6
          Given Some slow step N12 # features/steps/steps.php:2
          And Some normal step N13 # features/steps/steps.php:3

        Scenario: Second Scenario  # features/feature1.feature:10
          Given Some fast step N14 # features/steps/steps.php:4

      Feature: Second Feature

        Background:                  # features/feature2.feature:3
          Given Some normal step N21 # features/steps/steps.php:3

        Scenario: First Scenario     # features/feature2.feature:6
          Given Some slow step N22   # features/steps/steps.php:2
          And Some fast step N23     # features/steps/steps.php:4

      3 scenarios (3 passed)
      8 steps (8 passed)
      """

  Scenario: Second Name
    When I run "behat --no-ansi -f pretty --name 'Second Scenario'"
    Then it should pass with:
      """
      Feature: First Feature

        Background:                # features/feature1.feature:3
          Given Some slow step N11 # features/steps/steps.php:2

        Scenario: Second Scenario  # features/feature1.feature:10
          Given Some fast step N14 # features/steps/steps.php:4

      1 scenario (1 passed)
      2 steps (2 passed)
      """

  Scenario: RegEx
    When I run "behat --no-ansi -f pretty --name '/nd Scenario$/'"
    Then it should pass with:
      """
      Feature: First Feature

        Background:                # features/feature1.feature:3
          Given Some slow step N11 # features/steps/steps.php:2

        Scenario: Second Scenario  # features/feature1.feature:10
          Given Some fast step N14 # features/steps/steps.php:4

      1 scenario (1 passed)
      2 steps (2 passed)
      """
