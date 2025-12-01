Feature: I18n Attributes
  In order to write i18nal features
  As a feature writer
  I need to have i18n support using attributes

  Background:
    Given I initialise the working directory from the "I18n" fixtures folder
    And I provide the following options for all behat invocations:
      | option          | value                |
      | --no-colors     |                      |
      | --config        | behat-attributes.php |
      | --snippets-for  | FeatureContext       |
      | --snippets-type | regex                |

  Scenario: Pretty
    When I run behat with the following additional options:
      | option   | value  |
      | --format | pretty |
      | --lang   | ru     |
    Then it should fail with:
      """
      Функционал: Постоянство мира
        Чтобы поддерживать стабильными тесты
        Как разработчик функционала
        Я хочу чтобы Мир сбрасывался между сценариями

        Предыстория:     # features/World.feature:7
          Если Я ввел 10 # FeatureContextAttributes::iHaveEntered()

        Сценарий: Неопределен           # features/World.feature:10
          То Я должен иметь 10          # FeatureContextAttributes::iShouldHave()
          И Добавить "нормальное" число
          То Я должен иметь 10          # FeatureContextAttributes::iShouldHave()

        Сценарий: В ожидании      # features/World.feature:15
          То Я должен иметь 10    # FeatureContextAttributes::iShouldHave()
          И Что-то еще не сделано # FeatureContextAttributes::somethingNotDone()
            TODO: write pending definition
          То Я должен иметь 10    # FeatureContextAttributes::iShouldHave()

        Сценарий: Провален     # features/World.feature:20
          Если Я добавлю 4     # FeatureContextAttributes::iAdd()
          То Я должен иметь 13 # FeatureContextAttributes::iShouldHave()
            Failed asserting that 14 matches expected 13. (Exception)

        Структура сценария: Пройдено и Провалено # features/World.feature:24
          Допустим Я должен иметь 10             # FeatureContextAttributes::iShouldHave()
          Если Я добавлю <значение>              # FeatureContextAttributes::iAdd()
          То Я должен иметь <результат>          # FeatureContextAttributes::iShouldHave()

          Примеры:
            | значение | результат |
            | 5        | 16        |
              Проваленные шаг: То Я должен иметь 16
              Failed asserting that 15 matches expected 16. (Exception)
            | 10       | 20        |
            | 23       | 32        |
              Проваленные шаг: То Я должен иметь 32
              Failed asserting that 33 matches expected 32. (Exception)

      --- Проваленные сценарии:

          features/World.feature:20 (в строке 22)
          features/World.feature:31 (в строке 27)
          features/World.feature:33 (в строке 27)

      6 сценариев (1 пройден, 3 провалено, 1 не определен, 1 в ожидании)
      23 шага (16 пройдено, 3 провалено, 1 не определен, 1 в ожидании, 2 пропущено)

      --- Шаблоны для следующих шагов в среде default не были сгенерированы (проверьте ваши настройки):

          И Добавить "нормальное" число
      """

  Scenario: Progress
    When I run behat with the following additional options:
      | option   | value    |
      | --format | progress |
      | --lang   | ru       |
    Then it should fail with:
      """
      ..U-..P-..F...F.......F

      --- Проваленные шаги:

      001 Сценарий: Провален     # features/World.feature:20
            То Я должен иметь 13 # features/World.feature:22
              Failed asserting that 14 matches expected 13. (Exception)

      002 Example: | 5        | 16        | # features/World.feature:31
            То Я должен иметь 16            # features/World.feature:27
              Failed asserting that 15 matches expected 16. (Exception)

      003 Example: | 23       | 32        | # features/World.feature:33
            То Я должен иметь 32            # features/World.feature:27
              Failed asserting that 33 matches expected 32. (Exception)

      --- Шаги в ожидании:

      001 Сценарий: В ожидании      # features/World.feature:15
            И Что-то еще не сделано # FeatureContextAttributes::somethingNotDone()
              TODO: write pending definition

      6 сценариев (1 пройден, 3 провалено, 1 не определен, 1 в ожидании)
      23 шага (16 пройдено, 3 провалено, 1 не определен, 1 в ожидании, 2 пропущено)

      --- Шаблоны для следующих шагов в среде default не были сгенерированы (проверьте ваши настройки):

          И Добавить "нормальное" число
      """

  Scenario: Progress with unexisting locale
    When I run behat with the following additional options:
      | option   | value    |
      | --format | progress |
      | --lang   | xx       |
    Then it should fail with:
      """
      ..U-..P-..F...F.......F

      --- Failed steps:

      001 Сценарий: Провален     # features/World.feature:20
            То Я должен иметь 13 # features/World.feature:22
              Failed asserting that 14 matches expected 13. (Exception)

      002 Example: | 5        | 16        | # features/World.feature:31
            То Я должен иметь 16            # features/World.feature:27
              Failed asserting that 15 matches expected 16. (Exception)

      003 Example: | 23       | 32        | # features/World.feature:33
            То Я должен иметь 32            # features/World.feature:27
              Failed asserting that 33 matches expected 32. (Exception)

      --- Pending steps:

      001 Сценарий: В ожидании      # features/World.feature:15
            И Что-то еще не сделано # FeatureContextAttributes::somethingNotDone()
              TODO: write pending definition

      6 scenarios (1 passed, 3 failed, 1 undefined, 1 pending)
      23 steps (16 passed, 3 failed, 1 undefined, 1 pending, 2 skipped)

      --- Use --snippets-for CLI option to generate snippets for following default suite steps:

          И Добавить "нормальное" число
      """
