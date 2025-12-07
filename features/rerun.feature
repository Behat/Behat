Feature: Rerun
  In order to test only failed scenarios
  As a feature developer
  I need to have an ability to rerun failed previously scenarios

  Background:
    Given I initialise the working directory from the "Rerun" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value    |
      | --no-colors |          |
      | --format    | progress |

  Scenario: Run one feature with 2 failed and 3 passing scenarios
    When I run "behat features/apples.feature"
    Then it should fail with:
      """
      ..F.............F....

      --- Failed steps:

      001 Scenario: I'm little hungry   # features/apples.feature:9
            Then I should have 3 apples # features/apples.feature:11
              Failed asserting that 2 matches expected 3.

      002 Example: | 0   | 4     | 8      | # features/apples.feature:29
            Then I should have 8 apples     # features/apples.feature:24
              Failed asserting that 7 matches expected 8.

      6 scenarios (4 passed, 2 failed)
      21 steps (19 passed, 2 failed)
      """

  Scenario: Rerun only failed scenarios
    Given I run "behat features/apples.feature"
    When I run "behat features/apples.feature --rerun"
    Then it should fail with:
    """
    ..F...F

    --- Failed steps:

    001 Scenario: I'm little hungry   # features/apples.feature:9
          Then I should have 3 apples # features/apples.feature:11
            Failed asserting that 2 matches expected 3.

    002 Example: | 0   | 4     | 8      | # features/apples.feature:29
          Then I should have 8 apples     # features/apples.feature:24
            Failed asserting that 7 matches expected 8.

    2 scenarios (2 failed)
    7 steps (5 passed, 2 failed)
    """

  Scenario: Fixing scenario removes it from the rerun log
  Given I run "behat features/apples.feature"
  And I copy "features/apples-fixed.feature" to "features/apples.feature"
  When I run "behat features/apples.feature --rerun"
  Then it should fail with:
    """
    ..F....

    --- Failed steps:

    001 Scenario: I'm little hungry   # features/apples.feature:9
          Then I should have 3 apples # features/apples.feature:11
            Failed asserting that 2 matches expected 3.

    2 scenarios (1 passed, 1 failed)
    7 steps (6 passed, 1 failed)
    """
  And I run "behat features/apples.feature --rerun"
  And it should fail with:
    """
    ..F

    --- Failed steps:

    001 Scenario: I'm little hungry   # features/apples.feature:9
          Then I should have 3 apples # features/apples.feature:11
            Failed asserting that 2 matches expected 3.

    1 scenario (1 failed)
    3 steps (2 passed, 1 failed)
    """
