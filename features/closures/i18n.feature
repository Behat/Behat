Feature: I18n
  In order to write i18nal features
  As a feature writer
  I need to have i18n support

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
      $steps->Given('/Я ввел (\d+)/', function($world, $num) {
          assertObjectNotHasAttribute('value', $world);
          $world->value = $num;
      });

      $steps->Then('/Я должен иметь (\d+)/', function($world, $num) {
          assertEquals($num, $world->value);
      });

      $steps->When('/Я добавлю (\d+)/', function($world, $num) {
          $world->value += $num;
      });

      $steps->Given('/^Что-то еще не сделано$/', function($world) {
          throw new \Behat\Behat\Exception\PendingException();
      });
      """
    And a file named "features/World.feature" with:
      """
      # language: ru
      Функционал: Постоянство мира
        Чтобы поддерживать стабильными тесты
        Как разработчик функционала
        Я хочу чтобы Мир сбрасывался между сценариями

        Предыстория:
          Если Я ввел 10

        Сценарий: Неопределен
          То Я должен иметь 10
          И Что-то новое
          То Я должен иметь 10

        Сценарий: В ожидании
          То Я должен иметь 10
          И Что-то еще не сделано
          То Я должен иметь 10

        Сценарий: Провален
          Если Я добавлю 4
          То Я должен иметь 13

        Структура сценария: Пройдено и Провалено
          Допустим Я должен иметь 10
          Если Я добавлю <значение>
          То Я должен иметь <результат>

          Примеры:
            | значение | результат |
            |  5       | 16        |
            |  10      | 20        |
            |  23      | 32        |
      """

  Scenario: Pretty
    When I run "behat --no-ansi -f pretty --lang=ru"
    Then it should fail with:
      """
      Функционал: Постоянство мира
        Чтобы поддерживать стабильными тесты
        Как разработчик функционала
        Я хочу чтобы Мир сбрасывался между сценариями

        Предыстория:     # features/World.feature:7
          Если Я ввел 10 # features/steps/math.php:2

        Сценарий: Неопределен  # features/World.feature:10
          То Я должен иметь 10 # features/steps/math.php:7
          И Что-то новое
          То Я должен иметь 10 # features/steps/math.php:7

        Сценарий: В ожидании      # features/World.feature:15
          То Я должен иметь 10    # features/steps/math.php:7
          И Что-то еще не сделано # features/steps/math.php:15
            TODO: write pending definition
          То Я должен иметь 10    # features/steps/math.php:7

        Сценарий: Провален        # features/World.feature:20
          Если Я добавлю 4        # features/steps/math.php:11
          То Я должен иметь 13    # features/steps/math.php:7
            Failed asserting that 14 matches expected '13'.

        Структура сценария: Пройдено и Провалено # features/World.feature:24
          Допустим Я должен иметь 10             # features/steps/math.php:7
          Если Я добавлю <значение>              # features/steps/math.php:11
          То Я должен иметь <результат>          # features/steps/math.php:7

          Примеры:
            | значение | результат |
            | 5        | 16        |
              Failed asserting that 15 matches expected '16'.
            | 10       | 20        |
            | 23       | 32        |
              Failed asserting that 33 matches expected '32'.

      6 сценариев (1 пройден, 1 в ожидании, 1 не определен, 3 провалено)
      23 шага (16 пройдено, 2 пропущено, 1 в ожидании, 1 не определен, 3 провалено)

      Вы можете реализовать определения для новых шагов с помощью этих шаблонов:

      $steps->Given('/^Что-то новое$/', function($world) {
          throw new \Behat\Behat\Exception\PendingException();
      });
      """

  Scenario: Progress
    When I run "behat --no-ansi -f progress --lang=ru"
    Then it should fail with:
      """
      ..U-..P-..F...F.......F

      (::) проваленные шаги (::)

      01. Failed asserting that 14 matches expected '13'.
          In step `То Я должен иметь 13'. # features/steps/math.php:7
          From scenario `Провален'.       # features/World.feature:20
          Of feature `Постоянство мира'.  # features/World.feature

      02. Failed asserting that 15 matches expected '16'.
          In step `То Я должен иметь 16'.       # features/steps/math.php:7
          From scenario `Пройдено и Провалено'. # features/World.feature:24
          Of feature `Постоянство мира'.        # features/World.feature

      03. Failed asserting that 33 matches expected '32'.
          In step `То Я должен иметь 32'.       # features/steps/math.php:7
          From scenario `Пройдено и Провалено'. # features/World.feature:24
          Of feature `Постоянство мира'.        # features/World.feature

      (::) шаги в ожидании (::)

      01. TODO: write pending definition
          In step `И Что-то еще не сделано'.    # features/steps/math.php:15
          From scenario `В ожидании'.           # features/World.feature:15
          Of feature `Постоянство мира'.        # features/World.feature

      6 сценариев (1 пройден, 1 в ожидании, 1 не определен, 3 провалено)
      23 шага (16 пройдено, 2 пропущено, 1 в ожидании, 1 не определен, 3 провалено)

      Вы можете реализовать определения для новых шагов с помощью этих шаблонов:

      $steps->Given('/^Что-то новое$/', function($world) {
          throw new \Behat\Behat\Exception\PendingException();
      });
      """
