Feature: hooks
  In order to migrate Behat testing process
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

          public $number = 50;

          public function __construct(array $parameters) {
              $this->parameters = $parameters;
          }

          public function getStepDefinitionResources() {
              if (file_exists(__DIR__ . '/../steps')) {
                  $finder = new Finder();
                  return $finder->files()->name('*.php')->in(__DIR__ . '/../steps');
              }
              return array();
          }

          public function getHookDefinitionResources() {
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
    And a file named "features/steps/step1.php" with:
      """
      <?php
      require_once('step2.php');
      $steps->Given('/^I have entered (\d+)$/', function($world, $arg1) {
          $world->number = $arg1;
      });
      """
    And a file named "features/steps/step2.php" with:
      """
      <?php

      require_once('step1.php');
      $steps->Then('/^I must have (\d+)$/', function($world, $arg1) {
          assertEquals($world->number, $arg1);
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

        Scenario:
          Given I have entered 1
          Then I must have 1
      """
    When I run "behat --no-ansi -f pretty"
    Then it should pass with:
      """
      Feature:

        Scenario:             # features/test.feature:2
          Then I must have 50 # features/steps/step2.php:4

        Scenario:                 # features/test.feature:5
          Given I have entered 12 # features/steps/step1.php:3
          Then I must have 12     # features/steps/step2.php:4

        Scenario:                 # features/test.feature:9
          Given I have entered 1  # features/steps/step1.php:3
          Then I must have 1      # features/steps/step2.php:4

      3 scenarios (3 passed)
      5 steps (5 passed)
      """
