Feature: Skipped steps printing
  In order to test the print_skipped_steps option
  As a behat developer
  I need a scenario with a skipped step

  Scenario: Skips after a failure
    When a passing step
    And a failing step
    Then a skipped step
