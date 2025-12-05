Feature: Rerun
  In order to test only failed scenarios and exit if none is found
  As a feature developer
  I need to have an ability to rerun failed previously scenarios

  Background:
    Given I initialise the working directory from the "Rerun" fixtures folder
    And I provide the following options for all behat invocations:
      | option   | value    |
      | --no-colors |       |
      | --format | progress |

  Scenario: Rerun only failed scenarios
    Given I run "behat features/apples.feature"
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
    When I run "behat features/apples.feature --rerun-only"
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

  Scenario: Nothing is run if no rerun file is present
    Given I run "behat features/apples.feature --rerun-only"
    Then it should pass with:
      """
      No failure found, exiting
      """

  Scenario: Nothing is run if no failure was recorded in the rerun file
    When I run "behat features/apples-no-error.feature"
    Then it should pass with:
      """
      .....................

      6 scenarios (6 passed)
      21 steps (21 passed)
      """
    When I run "behat features/apples-no-error.feature --rerun-only"
    Then it should pass with:
    """
    No failure found, exiting
    """
