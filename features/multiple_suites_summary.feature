Feature: Suite names in the summary lists
  In order to identify the suite a scenario failed in when several suites share feature files
  As a feature developer
  I need the failure summaries to name the suite when more than one suite was executed

  Background:
    Given I initialise the working directory from the "MultipleSuitesSummary" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value |
      | --no-colors |       |

  Scenario: Failed steps are annotated with the suite name when several suites run
    When I run "behat --format progress"
    Then it should fail with:
      """
      ..F........F..F..F

      --- Failed steps:

      001 Scenario: I'm little hungry   # features/apples.feature:9 (default)
            Then I should have 3 apples # features/apples.feature:11
              Failed asserting that 2 matches expected 3.

      002 Example: | 1   | 8      |     # features/apples.feature:24 (default)
            Then I should have 8 apples # features/apples.feature:19
              Failed asserting that 2 matches expected 8.

      003 Scenario: I'm little hungry    # features/bananas.feature:9 (default)
            Then I should have 3 bananas # features/bananas.feature:11
              Failed asserting that 2 matches expected 3.

      004 Scenario: I'm little hungry    # features/bananas.feature:9 (suite2)
            Then I should have 3 bananas # features/bananas.feature:11
              Failed asserting that 2 matches expected 3.

      6 scenarios (2 passed, 4 failed)
      18 steps (14 passed, 4 failed)
      """

  Scenario: Failed scenarios are annotated with the suite name when several suites run
    When I run behat with the following additional options:
      | option            | value                     |
      | --format          | progress                  |
      | --format-settings | '{"short_summary": true}' |
    Then it should fail with:
      """
      ..F........F..F..F

      --- Failed scenarios:

          features/apples.feature:9 (default) (on line 11)
          features/apples.feature:24 (default) (on line 19)
          features/bananas.feature:9 (default) (on line 11)
          features/bananas.feature:9 (suite2) (on line 11)

      6 scenarios (2 passed, 4 failed)
      18 steps (14 passed, 4 failed)
      """

  Scenario: The pretty formatter annotates the failed scenarios too
    When I run "behat --format pretty"
    Then it should fail
    And the output should contain:
      """
      --- Failed scenarios:

          features/apples.feature:9 (default) (on line 11)
          features/apples.feature:24 (default) (on line 19)
          features/bananas.feature:9 (default) (on line 11)
          features/bananas.feature:9 (suite2) (on line 11)

      6 scenarios (2 passed, 4 failed)
      18 steps (14 passed, 4 failed)
      """

  Scenario: Suite names are omitted when a single suite runs
    When I run "behat --format progress --suite suite2"
    Then it should fail with:
      """
      ..F

      --- Failed steps:

      001 Scenario: I'm little hungry    # features/bananas.feature:9
            Then I should have 3 bananas # features/bananas.feature:11
              Failed asserting that 2 matches expected 3.

      1 scenario (1 failed)
      3 steps (2 passed, 1 failed)
      """
