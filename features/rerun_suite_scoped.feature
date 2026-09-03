Feature: Rerun only the suite in which the failure happened
  In order not to re-run scenarios that passed elsewhere
  As a feature developer
  I need the rerun cache to stay scoped to the suite the failure came from

  Background:
    Given I initialise the working directory from the "RerunSuiteScoped" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value    |
      | --no-colors |          |
      | --format    | progress |

  Scenario: A scenario that fails in one suite only is re-run there only
    Given I run "behat"
    Then it should fail with:
      """
      F...

      --- Failed steps:

      001 Scenario: one that only fails in one suite            # features/shared.feature:3 (failing)
            Given a step that only fails in the "failing" suite # features/shared.feature:4
              step failure (RuntimeException)

      4 scenarios (3 passed, 1 failed)
      4 steps (3 passed, 1 failed)
      """
    When I run "behat --rerun"
    Then it should fail with:
      """
      F

      --- Failed steps:

      001 Scenario: one that only fails in one suite            # features/shared.feature:3
            Given a step that only fails in the "failing" suite # features/shared.feature:4
              step failure (RuntimeException)

      1 scenario (1 failed)
      1 step (1 failed)
      """

  Scenario: An after-suite hook failure re-runs that suite and leaves the other one alone
    Given I run "behat -p afterSuite"
    Then it should fail with:
      """
      ....

      --- Failed hooks:

          AfterSuite "failing" # AfterSuiteContext::failAfterSuite()
            after suite hook failure (RuntimeException)

      4 scenarios (4 passed)
      4 steps (4 passed)
      """
    When I run "behat -p afterSuite --rerun"
    Then it should fail with:
      """
      ..

      --- Failed hooks:

          AfterSuite "failing" # AfterSuiteContext::failAfterSuite()
            after suite hook failure (RuntimeException)

      2 scenarios (2 passed)
      2 steps (2 passed)
      """
