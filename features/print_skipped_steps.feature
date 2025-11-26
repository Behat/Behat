Feature: Pretty formatter print_skipped_steps option
  In order to control whether skipped steps are printed
  As a Behat user
  I need a pretty formatter option to hide skipped steps

  Background:
    Given I initialise the working directory from the "PrintSkippedSteps" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value |
      | --no-colors |       |

  Scenario: Pretty prints skipped steps by default
    When I run behat with the following additional options:
      | option    | value   |
      | --profile | default |
    Then it should fail with:
      """
      Feature: Skipped steps printing
        In order to test the print_skipped_steps option
        As a behat developer
        I need a scenario with a skipped step

        Scenario: Skips after a failure
          When a passing step
          And a failing step
            step failed as supposed (Exception)
          Then a skipped step

      --- Failed scenarios:

          features/skip.feature:6 (on line 8)

      1 scenario (1 failed)
      3 steps (1 passed, 1 failed, 1 skipped)
      """

  Scenario: Pretty does not print skipped steps when disabled
    When I run behat with the following additional options:
      | option    | value        |
      | --profile | hide_skipped |
    Then it should fail with:
      """
      Feature: Skipped steps printing
        In order to test the print_skipped_steps option
        As a behat developer
        I need a scenario with a skipped step

        Scenario: Skips after a failure
          When a passing step
          And a failing step
            step failed as supposed (Exception)

      --- Failed scenarios:

          features/skip.feature:6 (on line 8)

      1 scenario (1 failed)
      3 steps (1 passed, 1 failed, 1 skipped)
      """
