Feature: Importing suites
  In order to add more suites
  As a feature writer
  I need an ability to import external suite configuration files

  Background:
    Given I initialise the working directory from the "ImportingSuites" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value |
      | --no-colors |       |

  Scenario: Importing one suite
    When I run behat with the following additional options:
      | option   | value                |
      | --config | behat-import-one.php |
      | --suite  | first                |
    Then it should pass with:
      """
      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Scenario: I'm little hungry   # features/some.feature:6
          Given I have 3 apples       # FirstContext::iHaveApples()
          When I ate 1 apple          # FirstContext::iAteApples()
          Then I should have 2 apples # FirstContext::iShouldHaveApples()

      1 scenario (1 passed)
      3 steps (3 passed)
      """

  Scenario: Importing two suites, running one
    When I run behat with the following additional options:
      | option   | value                |
      | --config | behat-import-two.php |
      | --suite  | first                |
    Then it should pass with:
      """
      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Scenario: I'm little hungry   # features/some.feature:6
          Given I have 3 apples       # FirstContext::iHaveApples()
          When I ate 1 apple          # FirstContext::iAteApples()
          Then I should have 2 apples # FirstContext::iShouldHaveApples()

      1 scenario (1 passed)
      3 steps (3 passed)
      """

  Scenario: Importing two suites, running all
    When I run behat with the following additional options:
      | option   | value                  |
      | --config | behat-import-array.php |
    Then it should pass with:
      """
      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Scenario: I'm little hungry   # features/some.feature:6
          Given I have 3 apples       # FirstContext::iHaveApples()
          When I ate 1 apple          # FirstContext::iAteApples()
          Then I should have 2 apples # FirstContext::iShouldHaveApples()

      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Scenario: I'm little hungry   # features/some.feature:6
          Given I have 3 apples       # SecondContext::iHaveApples()
          When I ate 1 apple          # SecondContext::iAteApples()
          Then I should have 2 apples # SecondContext::iShouldHaveApples()

      2 scenarios (2 passed)
      6 steps (6 passed)
      """
