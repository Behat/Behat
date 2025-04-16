Feature: Strict result interpretation defined in configuration file
  In order to consistently run strict tests in particular environments
  As a feature writer
  I need to be able to configure "strict" option in the configuration file

  Background:
    Given I set the working directory to the "ConfigStrictTesters" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value    |
      | --no-colors |          |
      | --format    | progress |

  Scenario: Not strict by default
    When  I run behat with the following additional options:
      | option    | value   |
      | --profile | default |
    Then it should pass with:
      """
      UUUUUU

      2 scenarios (2 undefined)
      6 steps (6 undefined)
      """

  Scenario: Not strict if configured false
    When  I run behat with the following additional options:
      | option    | value      |
      | --profile | not-strict |
    Then it should pass with:
      """
      UUUUUU

      2 scenarios (2 undefined)
      6 steps (6 undefined)
      """

  Scenario: Strict if configured true
    When  I run behat with the following additional options:
      | option    | value       |
      | --profile | with-strict |
    Then it should fail with:
      """
      UUUUUU

      2 scenarios (2 undefined)
      6 steps (6 undefined)
      """

  Scenario: --strict CLI flag takes precedence over configuring strict false
    When  I run behat with the following additional options:
      | option    | value      |
      | --profile | not-strict |
      | --strict  |            |
    Then it should fail with:
      """
      UUUUUU

      2 scenarios (2 undefined)
      6 steps (6 undefined)
      """
