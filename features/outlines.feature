Feature: Scenario Outlines
  In order to write complex features
  As a features writer
  I want to write scenario outlines

  Background:
    Given I initialise the working directory from the "Outlines" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value    |
      | --no-colors |          |
      | --format    | progress |

  Scenario: Basic scenario outline
    When I run "behat features/math-basic.feature"
    Then it should pass with:
      """
      ...............

      3 scenarios (3 passed)
      15 steps (15 passed)
      """

  Scenario: Multiple scenario outlines
    When I run "behat features/math-multiple.feature"
    Then it should pass with:
      """
      .........................

      5 scenarios (5 passed)
      25 steps (25 passed)
      """

  Scenario: Multiple scenario outlines with failing steps
    When I run "behat features/math-failing.feature"
    Then it should fail with:
      """
      .........F....F.........F....F

      --- Failed steps:

      001 Example: | 5       | 4       | 15     | # features/math-failing.feature:14
            Then The result should be 15          # features/math-failing.feature:9
              Failed asserting that 20 matches expected 15.

      002 Scenario:                     # features/math-failing.feature:16
            Then The result should be 7 # features/math-failing.feature:20
              Failed asserting that 6 matches expected 7.

      003 Example: | 50      | 10      | 2      | # features/math-failing.feature:31
            Then The result should be 2           # features/math-failing.feature:26
              Failed asserting that 5 matches expected 2.

      004 Example: | 50      | 10      | 4      | # features/math-failing.feature:32
            Then The result should be 4           # features/math-failing.feature:26
              Failed asserting that 5 matches expected 4.

      6 scenarios (2 passed, 4 failed)
      30 steps (26 passed, 4 failed)
      """

  Scenario: Outline with multiple examples and failing steps
    When I run "behat features/math-multiple-examples.feature"
    Then it should fail with:
      """
      .........F.........F.....

      --- Failed steps:

      001 Example: | 5       | 4       | 10     | # features/math-multiple-examples.feature:14
            Then The result should be 10          # features/math-multiple-examples.feature:9
              Failed asserting that 20 matches expected 10.

      002 Example: | 139     | 201     | 99     | # features/math-multiple-examples.feature:19
            Then The result should be 99          # features/math-multiple-examples.feature:9
              Failed asserting that 27939 matches expected 99.

      5 scenarios (3 passed, 2 failed)
      25 steps (23 passed, 2 failed)
      """

  Scenario: Scenario outline examples isolation
    When I run "behat --profile=isolation features/math-isolation.feature"
    Then it should pass with:
      """
      ......

      3 scenarios (3 passed)
      6 steps (6 passed)
      """
