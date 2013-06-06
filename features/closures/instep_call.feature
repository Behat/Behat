Feature: Call step in other step
  In order to mantain fluid step definition
  As a features writer
  I need to be able to call other steps from step body

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
    And a file named "features/support/env.php" with:
      """
      <?php
      $world->hash = array('username' => 'everzet', 'password' => 'qwerty');
      """
    And a file named "features/steps/calc_steps_en.php" with:
      """
      <?php
      $steps->Given('/I have entered "([^"]*)"/', function($world, $number) {
          $world->numbers[] = $number;
      });
      $steps->When('/I press +/', function($world) {
          $world->result  = array_sum($world->numbers);
          $world->numbers = array();
      });
      $steps->Then('/I should see "([^"]*)" on the screen/', function($world, $result) {
          assertEquals($result, $world->result);
      });
      $steps->Then('/Table should be:/', function($world, $table) {
          assertEquals($world->hash, $table->getRowsHash());
      });
      """
    And a file named "features/steps/calc_steps_ru.php" with:
      """
      <?php
      $steps->Допустим('/Я ввел "([^"]*)"/', function($world, $number) use($steps) {
          $steps->Given("I have entered \"$number\"", $world);
      });
      $steps->Если('/Я нажму +/', function($world) use($steps) {
          $steps->When("I press +", $world);
      });
      $steps->Тогда('/Я должен увидеть на экране "([^"]*)"/', function($world, $result) use($steps) {
          $steps->Then("I should see \"$result\" on the screen", $world);
      });
      $steps->Тогда('/Я создам себе failing таблицу/', function($world) use($steps) {
          $steps->Then('Table should be:', $world, new Behat\Gherkin\Node\TableNode(<<<TABLE
            | username | antono |
            | password | 123    |
      TABLE
          ));
      });
      $steps->Тогда('/Я создам себе passing таблицу/', function($world) use($steps) {
          $steps->Then('Table should be:', $world, new Behat\Gherkin\Node\TableNode(<<<TABLE
            | username | everzet |
            | password | qwerty  |
      TABLE
          ));
      });
      """

  Scenario:
    Given a file named "features/calc_en.feature" with:
      """
      Feature: Basic calculator
        Scenario:
          Given I have entered "12"
          And I have entered "27"
          And I have entered "5"
          When I press +
          Then I should see "44" on the screen
      """
    When I run "behat --no-ansi -f progress features/calc_en.feature"
    Then it should pass with:
      """
      .....

      1 scenario (1 passed)
      5 steps (5 passed)
      """

  Scenario:
    Given a file named "features/calc_en.feature" with:
      """
      Feature: Basic calculator
        Scenario:
          Given I have entered "7"
          When I press +
          Then I should see "8" on the screen
      """
    When I run "behat --no-ansi -f progress features/calc_en.feature"
    Then it should fail with:
      """
      ..F

      (::) failed steps (::)

      01. Failed asserting that 7 matches expected '8'.
          In step `Then I should see "8" on the screen'. # features/steps/calc_steps_en.php:9
          From scenario ***.                             # features/calc_en.feature:2
          Of feature `Basic calculator'.                 # features/calc_en.feature

      1 scenario (1 failed)
      3 steps (2 passed, 1 failed)
      """

  Scenario:
    Given a file named "features/calc_ru.feature" with:
      """
      # language: ru
      Функционал: Стандартный калькулятор
        Сценарий:
          Допустим Я ввел "12"
          И Я ввел "27"
          Если Я нажму +
          То Я должен увидеть на экране "39"
          И Я создам себе passing таблицу
      """
    When I run "behat --no-ansi -f progress features/calc_ru.feature"
    Then it should pass with:
      """
      .....

      1 scenario (1 passed)
      5 steps (5 passed)
      """

  Scenario:
    Given a file named "features/calc_ru.feature" with:
      """
      # language: ru
      Функционал: Стандартный калькулятор
        Сценарий:
          Допустим Я ввел "7"
          Если Я нажму +
          То Я должен увидеть на экране "8"

        Сценарий:
          Допустим Я создам себе failing таблицу
      """
    When I run "behat --no-ansi -f progress features/calc_ru.feature"
    Then it should fail with:
      """
      ..FF

      (::) failed steps (::)

      01. Failed asserting that 7 matches expected '8'.
          In step `То Я должен увидеть на экране "8"'. # features/steps/calc_steps_ru.php:8
          From scenario ***.                           # features/calc_ru.feature:3
          Of feature `Стандартный калькулятор'.        # features/calc_ru.feature

      02. Failed asserting that two arrays are equal.
          --- Expected
          +++ Actual
          @@ @@
           Array (
          -    'username' => 'everzet'
          -    'password' => 'qwerty'
          +    'username' => 'antono'
          +    'password' => '123'
           )
          In step `Допустим Я создам себе failing таблицу'. # features/steps/calc_steps_ru.php:11
          From scenario ***.                                # features/calc_ru.feature:8
          Of feature `Стандартный калькулятор'.             # features/calc_ru.feature

      2 scenarios (2 failed)
      4 steps (2 passed, 2 failed)
      """
