Feature: hooks
  In order to hook into Behat testing process
  As a tester
  I need to be able to write hooks

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
              if (file_exists(__DIR__ . '/../support/')) {
                  $finder = new Finder();
                  return $finder->files()->name('hooks*.php')->in(__DIR__ . '/../support');
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
      $steps->Given('/^I have entered (\d+)$/', function($world, $arg1) {
          $world->number = $arg1;
      });
      $steps->Then('/^I must have (\d+)$/', function($world, $arg1) {
          assertEquals($world->number, $arg1);
      });
      """
    And a file named "features/support/hooks_before.php" with:
      """
      <?php
      require_once('hooks_after.php');
      $hooks->beforeSuite(function($event) {
          echo "= do something before all suite run\n";
      });
      $hooks->beforeScenario('', function($event) {
          $env = $event->getContext();
          $env->number = 50;
      });
      $hooks->beforeScenario('130', function($event) {
          $env = $event->getContext();
          $env->number = 130;
      });
      $hooks->beforeScenario('@thirty', function($event) {
          $env = $event->getContext();
          $env->number = 30;
      });
      """
    And a file named "features/support/hooks_after.php" with:
      """
      <?php
      require_once('hooks_before.php');
      $hooks->afterSuite(function($event) {
          echo "= do something after all suite run\n";
      });
      $hooks->afterStep('@100', function($event) {
          $env = $event->getContext();
          $env->number = 100;
      });
      """


  Scenario:
    Given a file named "features/test.feature" with:
      """
      Feature:
        Scenario:
          Then I must have 50
        Scenario:
          Given I have entered 12
          Then I must have 12

        @thirty
        Scenario:
          Given I must have 30
          When I have entered 23
          Then I must have 23
        @100 @thirty
        Scenario:
          Given I must have 30
          When I have entered 1
          Then I must have 100

        Scenario: 130
          Given I must have 130
      """
    When I run "behat --no-ansi -f pretty"
    Then it should pass with:
      """
      = do something before all suite run
      Feature:

        Scenario:             # features/test.feature:2
          Then I must have 50 # features/steps/steps.php:5

        Scenario:                 # features/test.feature:4
          Given I have entered 12 # features/steps/steps.php:2
          Then I must have 12     # features/steps/steps.php:5

        @thirty
        Scenario:                 # features/test.feature:9
          Given I must have 30    # features/steps/steps.php:5
          When I have entered 23  # features/steps/steps.php:2
          Then I must have 23     # features/steps/steps.php:5

        @100 @thirty
        Scenario:                 # features/test.feature:14
          Given I must have 30    # features/steps/steps.php:5
          When I have entered 1   # features/steps/steps.php:2
          Then I must have 100    # features/steps/steps.php:5

        Scenario: 130             # features/test.feature:19
          Given I must have 130   # features/steps/steps.php:5

      = do something after all suite run
      5 scenarios (5 passed)
      10 steps (10 passed)
      """
