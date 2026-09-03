Feature: Rerun scenarios that failed in an after hook
  In order to re-run only what actually failed
  As a feature developer
  I need a scenario that fails through an after-scenario hook to be recorded for --rerun

  Background:
    Given I initialise the working directory from the "RerunAfterHooks" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value    |
      | --no-colors |          |
      | --format    | progress |

  Scenario: A scenario failing only in an after-scenario hook is re-run on its own
    # The summary still counts the scenario as passed: that is the separate concern of #1536.
    Given I run "behat"
    Then it should fail with:
      """
      ..

      --- Failed hooks:

          AfterScenario @failing-after-scenario-hook "features/hooks.feature:4" # FeatureContext::failAfterScenario()
            after scenario hook failure (RuntimeException)

      2 scenarios (2 passed)
      2 steps (2 passed)
      """
    When I run "behat --rerun"
    Then it should fail with:
      """
      .

      --- Failed hooks:

          AfterScenario @failing-after-scenario-hook "features/hooks.feature:4" # FeatureContext::failAfterScenario()
            after scenario hook failure (RuntimeException)

      1 scenario (1 passed)
      1 step (1 passed)
      """
