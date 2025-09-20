Feature: show short or long summaries
  In order to see the information that is more relevant to my needs
  As a developer
  I need to be able to ask Behat to print a short or long summary

  Background:
    Given I initialise the working directory from the "ShortSummary" fixtures folder
    And I provide the following options for all behat invocations:
      | option          | value            |
      | --no-colors     |                  |
      | --snippets-for  | FeatureContext   |

  Scenario: Print long summary in pretty printer
    When I run behat with the following additional options:
      | option    | value                    |
      | --profile | pretty_with_long_summary |
    Then the output should contain:
      """
      --- Failed hooks:

          AfterSuite "default" # FeatureContext::tearDown()

      --- Failed steps:

      001 Scenario:                   # features/test.feature:7
            And I have a failing step # features/test.feature:9
              This scenario has a failed step (Exception)

      --- Pending steps:

      001 Scenario:                     # features/test.feature:3
            Given I have a pending step # FeatureContext::iHaveAPendingStep()
              TODO: write pending definition

      2 scenarios (1 failed, 1 undefined)
      4 steps (1 passed, 1 failed, 1 undefined, 1 pending)

      --- FeatureContext has missing steps. Define them with these snippets:

          #[Given('I have an undefined step')]
          public function iHaveAnUndefinedStep(): void
          {
              throw new PendingException();
          }
      """

  Scenario: Print short summary in progress printer
    When I run behat with the following additional options:
      | option    | value                       |
      | --profile | progress_with_short_summary |
    Then the output should contain:
      """
      --- Failed hooks:

          AfterSuite "default" # FeatureContext::tearDown()
            This suite has a failed teardown (Exception)

      --- Failed scenarios:

          features/test.feature:7 (on line 9)

      2 scenarios (1 failed, 1 undefined)
      4 steps (1 passed, 1 failed, 1 undefined, 1 pending)

      --- FeatureContext has missing steps. Define them with these snippets:

          #[Given('I have an undefined step')]
          public function iHaveAnUndefinedStep(): void
          {
              throw new PendingException();
          }
      """

  Scenario: Option can be set on the command line
    When I run behat with the following additional options:
      | option            | value                      |
      | --format-settings | '{"short_summary": false}' |
    Then the output should contain:
      """
      --- Failed steps:
      """
