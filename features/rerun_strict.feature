Feature: Rerun with strict
  In order to test only failed scenarios with the strict option
  As a feature developer
  I need to have an ability to rerun failed previously scenarios, including those which failed due to the strict option

  Background:
    Given I initialise the working directory from the "RerunStrict" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value    |
      | --no-colors |          |
      | --format    | progress |

  Scenario: Rerun feature without strict option
    When I run "behat features/rerun_strict.feature"
    Then it should fail with:
      """
      UF.

      --- Failed steps:

      001 Scenario: failing step       # features/rerun_strict.feature:6
            When I have a failing step # features/rerun_strict.feature:7
              (Exception)

      3 scenarios (1 passed, 1 failed, 1 undefined)
      3 steps (1 passed, 1 failed, 1 undefined)
      """
    And I run "behat --rerun features/rerun_strict.feature"
    Then it should fail with:
      """
      F

      --- Failed steps:

      001 Scenario: failing step       # features/rerun_strict.feature:6
            When I have a failing step # features/rerun_strict.feature:7
              (Exception)

      1 scenario (1 failed)
      1 step (1 failed)
      """

    Scenario: Rerun feature with strict option
    When I run "behat --strict features/rerun_strict.feature"
    Then it should fail with:
      """
      UF.

      --- Failed steps:

      001 Scenario: failing step       # features/rerun_strict.feature:6
            When I have a failing step # features/rerun_strict.feature:7
              (Exception)

      3 scenarios (1 passed, 1 failed, 1 undefined)
      3 steps (1 passed, 1 failed, 1 undefined)
      """
    And I run "behat --strict --rerun features/rerun_strict.feature"
    Then it should fail with:
      """
      UF

      --- Failed steps:

      001 Scenario: failing step       # features/rerun_strict.feature:6
            When I have a failing step # features/rerun_strict.feature:7
              (Exception)

      2 scenarios (1 failed, 1 undefined)
      2 steps (1 failed, 1 undefined)
      """
