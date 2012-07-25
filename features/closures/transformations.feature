Feature: Step Arguments Transformations
  In order to follow DRY
  As a feature writer
  I need to be able to move common
  arguments transformations
  into transformation functions

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
    And a file named "features/steps/user.php" with:
      """
      <?php
      class User
      {
          private $username;
          private $age;

          public function __construct($username, $age = 20) { $this->username = $username; $this->age = $age; }
          public function getUsername() { return $this->username; }
          public function getAge() { return $this->age; }
      }

      $steps->Transform('/"([^"]+)" user/', function($username) {
          return new User($username);
      });

      $steps->Transform('/^table:username,age$/', function($table) {
          $hash     = $table->getHash();
          $username = $hash[0]['username'];
          $age      = $hash[0]['age'];

          return new User($username, $age);
      });

      $steps->Given('/I am ("\w+" user)/', function($world, $user) {
          assertInstanceOf('User', $user);
          $world->user = $user;
      });

      $steps->Given('/I am user:/', function($world, $user) {
          $world->user = $user;
      });

      $steps->Then('/Username must be "([^"]+)"/', function($world, $username) {
          assertEquals($username, $world->user->getUsername());
      });

      $steps->Then('/Age must be (\d+)/', function($world, $age) {
          assertEquals($age, $world->user->getAge());
      });
      """

  Scenario: Simple Arguments Transformations
    Given a file named "features/step_arguments.feature" with:
      """
      Feature: Step Arguments
        Scenario:
          Given I am "everzet" user
          Then Username must be "everzet"
          And Age must be 20

        Scenario:
          Given I am "antono" user
          Then Username must be "antono"
          And Age must be 20
      """
    When I run "behat --no-ansi -f progress"
    Then it should pass with:
      """
      ......

      2 scenarios (2 passed)
      6 steps (6 passed)
      """

  Scenario: Table Arguments Transformations
    Given a file named "features/table_arguments.feature" with:
      """
      Feature: Step Arguments
        Scenario:
          Given I am user:
            | username | age |
            | ever.zet | 22  |
          Then Username must be "ever.zet"
          And Age must be 22

        Scenario:
          Given I am user:
            | username | age |
            | vasiljev | 30  |
          Then Username must be "vasiljev"
          And Age must be 30
      """
    When I run "behat --no-ansi -f progress"
    Then it should pass with:
      """
      ......

      2 scenarios (2 passed)
      6 steps (6 passed)
      """

