Feature: Stop on failure via config
  In order to stop further execution of steps when first step fails
  As a feature developer
  I need to have a stop-on-failure option on the CLI or in config

  Background:
    Given I set the working directory to the "StopOnFailure" fixtures folder
    And I provide the following options for all behat invocations:
      | option            | value              |
      | --no-colors       |                    |
      | --format-settings | '{"paths": false}' |

  Scenario: Run all scenarios with with default config and command options
    When  I run behat with the following additional options:
      | option                   | value   |
      | --profile                | default |
      | features/failing.feature |         |
    Then it should fail with:
      """
      --- Failed scenarios:

          features/failing.feature:13
          features/failing.feature:18

      4 scenarios (2 passed, 2 failed)
      14 steps (10 passed, 2 failed, 2 skipped)
      """

  Scenario: Run all scenarios with stop_on_failure set to false in config
    When  I run behat with the following additional options:
      | option                   | value              |
      | --profile                | no-stop-on-failure |
      | features/failing.feature |                    |
    Then it should fail with:
      """
      --- Failed scenarios:

          features/failing.feature:13
          features/failing.feature:18

      4 scenarios (2 passed, 2 failed)
      14 steps (10 passed, 2 failed, 2 skipped)
      """


  Scenario: Stop on first failure with stop_on_failure set to true in config
    When  I run behat with the following additional options:
      | option                   | value           |
      | --profile                | stop-on-failure |
      | features/failing.feature |                 |
    Then it should fail with:
      """
      --- Failed scenarios:

          features/failing.feature:13

      2 scenarios (1 passed, 1 failed)
      7 steps (5 passed, 1 failed, 1 skipped)
      """

  Scenario: Stop on first failure with --stop-on-failure on CLI
    When  I run behat with the following additional options:
      | option                   | value |
      | --stop-on-failure        |       |
      | features/failing.feature |       |
    Then it should fail with:
      """
      --- Failed scenarios:

          features/failing.feature:13

      2 scenarios (1 passed, 1 failed)
      7 steps (5 passed, 1 failed, 1 skipped)
      """

  Scenario: --stop-on-failure CLI flag takes precedence over configuring stop_on_failure false
    When  I run behat with the following additional options:
      | option                   | value              |
      | --profile                | no-stop-on-failure |
      | --stop-on-failure        |                    |
      | features/failing.feature |                    |
    Then it should fail with:
      """
      --- Failed scenarios:

          features/failing.feature:13

      2 scenarios (1 passed, 1 failed)
      7 steps (5 passed, 1 failed, 1 skipped)
      """

  Scenario: Do not stop on undefined steps by default
    When  I run behat with the following additional options:
      | option                        | value           |
      | --profile                     | stop-on-failure |
      | features/missing-step.feature |                 |
    Then it should pass with:
      """
      2 scenarios (1 passed, 1 undefined)
      6 steps (4 passed, 1 undefined, 1 skipped)
      """

  Scenario: Stop on first undefined step in strict mode
    When  I run behat with the following additional options:
      | option                        | value           |
      | --profile                     | stop-on-failure |
      | --strict                      |                 |
      | features/missing-step.feature |                 |
    Then it should fail with:
      """
      1 scenario (1 undefined)
      3 steps (1 passed, 1 undefined, 1 skipped)
      """

  Scenario: Run all scenarios if none fail, even with stop_on_failure set
    When  I run behat with the following additional options:
      | option                   | value           |
      | --profile                | stop-on-failure |
      | features/passing.feature |                 |
    Then it should pass with:
      """
      3 scenarios (3 passed)
      12 steps (12 passed)
      """
