Feature: I18n
  In order to write i18nal features
  As a feature writer
  I need to have i18n support

  Background:
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\CustomSnippetAcceptingContext,
          Behat\Behat\Tester\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeatureContext implements CustomSnippetAcceptingContext
      {
          private $value = 0;

          public static function getAcceptedSnippetType() { return 'regex'; }

          /**
           * @Given /Я ввел (\d+)/
           */
          public function iHaveEntered($number) {
              $this->value = intval($number);
          }

          /**
           * @Then /Я должен иметь (\d+)/
           */
          public function iShouldHave($number) {
              PHPUnit_Framework_Assert::assertEquals(intval($number), $this->value);
          }

          /**
           * @When /Я добавлю (\d+)/
           */
          public function iAdd($number) {
              $this->value += intval($number);
          }

          /**
           * @When /^Что-то еще не сделано$/
           */
          public function somethingNotDone() {
              throw new PendingException();
          }
      }
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
          И Добавить "нормальное" число
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
    When I run "behat --no-colors -f pretty --lang=ru"
    Then it should fail with:
      """
      Функционал: Постоянство мира
        Чтобы поддерживать стабильными тесты
        Как разработчик функционала
        Я хочу чтобы Мир сбрасывался между сценариями

        Предыстория:     # features/World.feature:7
          Если Я ввел 10 # FeatureContext::iHaveEntered()

        Сценарий: Неопределен           # features/World.feature:10
          То Я должен иметь 10          # FeatureContext::iShouldHave()
          И Добавить "нормальное" число
          То Я должен иметь 10          # FeatureContext::iShouldHave()

        Сценарий: В ожидании      # features/World.feature:15
          То Я должен иметь 10    # FeatureContext::iShouldHave()
          И Что-то еще не сделано # FeatureContext::somethingNotDone()
            TODO: write pending definition
          То Я должен иметь 10    # FeatureContext::iShouldHave()

        Сценарий: Провален     # features/World.feature:20
          Если Я добавлю 4     # FeatureContext::iAdd()
          То Я должен иметь 13 # FeatureContext::iShouldHave()
            Failed asserting that 14 matches expected 13.

        Структура сценария: Пройдено и Провалено # features/World.feature:24
          Допустим Я должен иметь 10             # FeatureContext::iShouldHave()
          Если Я добавлю <значение>              # FeatureContext::iAdd()
          То Я должен иметь <результат>          # FeatureContext::iShouldHave()

          Примеры:
            | значение | результат |
            | 5        | 16        |
              Failed asserting that 15 matches expected 16.
            | 10       | 20        |
            | 23       | 32        |
              Failed asserting that 33 matches expected 32.

      --- Проваленные сценарии:

          features/World.feature:20
          features/World.feature:31
          features/World.feature:33

      6 сценариев (1 пройден, 3 провалено, 1 не определен, 1 в ожидании)
      23 шага (16 пройдено, 3 провалено, 1 не определен, 1 в ожидании, 2 пропущено)

      --- FeatureContext не содержит необходимых определений. Вы можете добавить их используя шаблоны:

          /**
           * @Then /^Добавить "([^"]*)" число$/
           */
          public function dobavitChislo($arg1)
          {
              throw new PendingException();
          }
      """

  Scenario: Progress
    When I run "behat --no-colors -f progress --lang=ru"
    Then it should fail with:
      """
      ..U-..P-..F...F.......F

      --- Проваленные шаги:

          То Я должен иметь 13 # features/World.feature:22
            Failed asserting that 14 matches expected 13.

          То Я должен иметь 16 # features/World.feature:27
            Failed asserting that 15 matches expected 16.

          То Я должен иметь 32 # features/World.feature:27
            Failed asserting that 33 matches expected 32.

      --- Шаги в ожидании:

          И Что-то еще не сделано # FeatureContext::somethingNotDone()
            TODO: write pending definition

      6 сценариев (1 пройден, 3 провалено, 1 не определен, 1 в ожидании)
      23 шага (16 пройдено, 3 провалено, 1 не определен, 1 в ожидании, 2 пропущено)

      --- FeatureContext не содержит необходимых определений. Вы можете добавить их используя шаблоны:

          /**
           * @Then /^Добавить "([^"]*)" число$/
           */
          public function dobavitChislo($arg1)
          {
              throw new PendingException();
          }
      """

  Scenario: Progress with unexisting locale
    When I run "behat --no-colors -f progress --lang=xx"
    Then it should fail with:
      """
      ..U-..P-..F...F.......F

      --- Failed steps:

          То Я должен иметь 13 # features/World.feature:22
            Failed asserting that 14 matches expected 13.

          То Я должен иметь 16 # features/World.feature:27
            Failed asserting that 15 matches expected 16.

          То Я должен иметь 32 # features/World.feature:27
            Failed asserting that 33 matches expected 32.

      --- Pending steps:

          И Что-то еще не сделано # FeatureContext::somethingNotDone()
            TODO: write pending definition

      6 scenarios (1 passed, 3 failed, 1 undefined, 1 pending)
      23 steps (16 passed, 3 failed, 1 undefined, 1 pending, 2 skipped)

      --- FeatureContext has missing steps. Define them with these snippets:

          /**
           * @Then /^Добавить "([^"]*)" число$/
           */
          public function dobavitChislo($arg1)
          {
              throw new PendingException();
          }
      """

  Scenario: Progress with unexisting locale
    When I run "behat --no-colors -f progress --lang=xx"
    Then it should fail with:
      """
      ..U-..P-..F...F.......F

      --- Failed steps:

          То Я должен иметь 13 # features/World.feature:22
            Failed asserting that 14 matches expected 13.

          То Я должен иметь 16 # features/World.feature:27
            Failed asserting that 15 matches expected 16.

          То Я должен иметь 32 # features/World.feature:27
            Failed asserting that 33 matches expected 32.

      --- Pending steps:

          И Что-то еще не сделано # FeatureContext::somethingNotDone()
            TODO: write pending definition

      6 scenarios (1 passed, 3 failed, 1 undefined, 1 pending)
      23 steps (16 passed, 3 failed, 1 undefined, 1 pending, 2 skipped)

      --- FeatureContext has missing steps. Define them with these snippets:

          /**
           * @Then /^Добавить "([^"]*)" число$/
           */
          public function dobavitChislo($arg1)
          {
              throw new PendingException();
          }
      """
