Feature: Progress formatter inline failures
  In order to diagnose problems during long progress-formatter runs
  As a developer
  I need the option to print failing, pending and undefined steps inline as they happen

  Background:
    Given I initialise the working directory from the "PrintInlineFailures" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value |
      | --no-colors |       |

  Scenario: Step problems are only listed in the summary by default
    When I run "behat --format=progress features/inline.feature"
    Then it should fail with:
      """
      .FPU

      --- Failed steps:
      """

  Scenario: Problems are printed inline when enabled via format settings
    When I run "behat --format=progress --format-settings='{\"inline_failures\": true}' features/inline.feature"
    Then it should fail with:
      """
      .
      --- FAILED ---
          And a failing step # features/inline.feature:5
            step failed as supposed (RuntimeException)
      ------------
      --- PENDING ---
          Given a pending step # features/inline.feature:8
            TODO: write pending definition
      ------------
      --- UNDEFINED ---
          Given an undefined step # features/inline.feature:11
      ------------
      """

  Scenario: Inline failures can be enabled in the configuration file
    When I run "behat --profile=inline --format=progress features/inline.feature"
    Then it should fail with:
      """
      .
      --- FAILED ---
          And a failing step # features/inline.feature:5
            step failed as supposed (RuntimeException)
      ------------
      --- PENDING ---
          Given a pending step # features/inline.feature:8
            TODO: write pending definition
      ------------
      --- UNDEFINED ---
          Given an undefined step # features/inline.feature:11
      ------------
      """
