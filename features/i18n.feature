Feature: I18n
  In order to write i18nal features
  As a feature writer
  I need to have i18n support

  Background:
    Given a file named "features/support/bootstrap.php" with:
      """
      <?php
      require_once 'PHPUnit/Autoload.php';
      require_once 'PHPUnit/Framework/Assert/Functions.php';
      """
    And a file named "features/steps/math.php" with:
      """
      <?php
      $steps->Given('/Я ввел (\d+)/', function($world, $num) {
          assertNull($world->value);
          $world->value = $num;
      });

      $steps->Then('/Я должен иметь (\d+)/', function($world, $num) {
          assertEquals($num, $world->value);
      });

      $steps->When('/Я добавлю (\d+)/', function($world, $num) {
          $world->value += $num;
      });

      $steps->And('/^Что-то еще не сделано$/', function($world) {
          throw new \Behat\Behat\Exception\Pending();
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

          Значения:
            | значение | результат |
            |  5       | 16        |
            |  10      | 20        |
            |  23      | 32        |
      """

  Scenario: Pretty
    When I run "behat -TCf pretty --lang=ru"
    Then it should fail with:
      """
      Функционал: Постоянство мира
        Чтобы поддерживать стабильными тесты
        Как разработчик функционала
        Я хочу чтобы Мир сбрасывался между сценариями
      
        Предыстория:     # features/World.feature:7
          Если Я ввел 10 # features/steps/math.php:5
      
        Сценарий: Неопределен  # features/World.feature:10
          То Я должен иметь 10 # features/steps/math.php:9
          И Что-то новое
          То Я должен иметь 10 # features/steps/math.php:9
      
        Сценарий: В ожидании      # features/World.feature:15
          То Я должен иметь 10    # features/steps/math.php:9
          И Что-то еще не сделано # features/steps/math.php:17
            TODO: write pending definition
          То Я должен иметь 10    # features/steps/math.php:9
      
        Сценарий: Провален        # features/World.feature:20
          Если Я добавлю 4        # features/steps/math.php:13
          То Я должен иметь 13    # features/steps/math.php:9
            Failed asserting that <integer:14> is equal to <string:13>.
      
        Структура сценария: Пройдено и Провалено # features/World.feature:24
          Допустим Я должен иметь 10             # features/steps/math.php:9
          Если Я добавлю <значение>              # features/steps/math.php:13
          То Я должен иметь <результат>          # features/steps/math.php:9
      
          Значения:
            | значение | результат |
            | 5        | 16        |
              Failed asserting that <integer:15> is equal to <string:16>.
            | 10       | 20        |
            | 23       | 32        |
              Failed asserting that <integer:33> is equal to <string:32>.
      
      6 сценариев (1 пройден, 1 в ожидании, 1 не определен, 3 провалено)
      23 шага (16 пройдено, 2 пропущено, 1 в ожидании, 1 не определен, 3 провалено)
      
      Вы можете реализовать определения для новых шагов с помощью этих шаблонов:
      
      $steps->И('/^Что-то новое$/', function($world) {
          throw new \Behat\Behat\Exception\Pending();
      });
      """

  Scenario: Progress
    When I run "behat -TCf progress --lang=ru"
    Then it should fail with:
      """
      ..U-..P-..F...F.......F
      
      (::) проваленные шаги (::)
      
      01. Failed asserting that <integer:14> is equal to <string:13>.
          In step `То Я должен иметь 13'. # features/steps/math.php:9
          From scenario `Провален'.       # features/World.feature:20
      
      02. Failed asserting that <integer:15> is equal to <string:16>.
          In step `То Я должен иметь 32'.       # features/steps/math.php:9
          From scenario `Пройдено и Провалено'. # features/World.feature:24
      
      03. Failed asserting that <integer:33> is equal to <string:32>.
          In step `То Я должен иметь 32'.       # features/steps/math.php:9
          From scenario `Пройдено и Провалено'. # features/World.feature:24
      
      (::) шаги в ожидании (::)
      
      01. TODO: write pending definition
          In step `И Что-то еще не сделано'.    # features/steps/math.php:17
          From scenario `В ожидании'.           # features/World.feature:15
      
      6 сценариев (1 пройден, 1 в ожидании, 1 не определен, 3 провалено)
      23 шага (16 пройдено, 2 пропущено, 1 в ожидании, 1 не определен, 3 провалено)
      
      Вы можете реализовать определения для новых шагов с помощью этих шаблонов:
      
      $steps->И('/^Что-то новое$/', function($world) {
          throw new \Behat\Behat\Exception\Pending();
      });
      """
