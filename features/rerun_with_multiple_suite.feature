Feature: Rerun with multiple suite
  In order to test only failed scenarios in different suite
  As a feature developer
  I need to have an ability to rerun failed previously scenarios

  Background:
    Given I initialise the working directory from the "Rerun" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value    |
      | --no-colors |          |
      | --format    | progress |

  Scenario: Rerun only failed scenarios, 4 from default suite and 2 from suite2
    Given I run "behat"
    Then it should fail with:
      """
      ..F.............F......F.............F......F.............F....

      --- Failed steps:

      001 Scenario: I'm little hungry   # features/apples.feature:9
            Then I should have 3 apples # features/apples.feature:11
              Failed asserting that 2 matches expected 3.

      002 Example: | 0   | 4     | 8      | # features/apples.feature:29
            Then I should have 8 apples     # features/apples.feature:24
              Failed asserting that 7 matches expected 8.

      003 Scenario: I'm little hungry    # features/bananas.feature:9
            Then I should have 3 bananas # features/bananas.feature:11
              Failed asserting that 2 matches expected 3.

      004 Example: | 0   | 4     | 8      | # features/bananas.feature:29
            Then I should have 8 bananas    # features/bananas.feature:24
              Failed asserting that 7 matches expected 8.

      005 Scenario: I'm little hungry    # features/bananas.feature:9
            Then I should have 3 bananas # features/bananas.feature:11
              Failed asserting that 2 matches expected 3.

      006 Example: | 0   | 4     | 8      | # features/bananas.feature:29
            Then I should have 8 bananas    # features/bananas.feature:24
              Failed asserting that 7 matches expected 8.

      18 scenarios (12 passed, 6 failed)
      63 steps (57 passed, 6 failed)
      """
    When I run "behat --rerun"
    Then it should fail with:
    """
    ..F...F..F...F..F...F

    --- Failed steps:

    001 Scenario: I'm little hungry   # features/apples.feature:9
          Then I should have 3 apples # features/apples.feature:11
            Failed asserting that 2 matches expected 3.

    002 Example: | 0   | 4     | 8      | # features/apples.feature:29
          Then I should have 8 apples     # features/apples.feature:24
            Failed asserting that 7 matches expected 8.

    003 Scenario: I'm little hungry    # features/bananas.feature:9
          Then I should have 3 bananas # features/bananas.feature:11
            Failed asserting that 2 matches expected 3.

    004 Example: | 0   | 4     | 8      | # features/bananas.feature:29
          Then I should have 8 bananas    # features/bananas.feature:24
            Failed asserting that 7 matches expected 8.

    005 Scenario: I'm little hungry    # features/bananas.feature:9
          Then I should have 3 bananas # features/bananas.feature:11
            Failed asserting that 2 matches expected 3.

    006 Example: | 0   | 4     | 8      | # features/bananas.feature:29
          Then I should have 8 bananas    # features/bananas.feature:24
            Failed asserting that 7 matches expected 8.

    6 scenarios (6 failed)
    21 steps (15 passed, 6 failed)
    """

  Scenario: Fixing scenarios removes it from the rerun log
    Given I run "behat"
    Then it should fail with:
      """
      ..F.............F......F.............F......F.............F....

      --- Failed steps:

      001 Scenario: I'm little hungry   # features/apples.feature:9
            Then I should have 3 apples # features/apples.feature:11
              Failed asserting that 2 matches expected 3.

      002 Example: | 0   | 4     | 8      | # features/apples.feature:29
            Then I should have 8 apples     # features/apples.feature:24
              Failed asserting that 7 matches expected 8.

      003 Scenario: I'm little hungry    # features/bananas.feature:9
            Then I should have 3 bananas # features/bananas.feature:11
              Failed asserting that 2 matches expected 3.

      004 Example: | 0   | 4     | 8      | # features/bananas.feature:29
            Then I should have 8 bananas    # features/bananas.feature:24
              Failed asserting that 7 matches expected 8.

      005 Scenario: I'm little hungry    # features/bananas.feature:9
            Then I should have 3 bananas # features/bananas.feature:11
              Failed asserting that 2 matches expected 3.

      006 Example: | 0   | 4     | 8      | # features/bananas.feature:29
            Then I should have 8 bananas    # features/bananas.feature:24
              Failed asserting that 7 matches expected 8.

      18 scenarios (12 passed, 6 failed)
      63 steps (57 passed, 6 failed)
      """
    And I copy "features/bananas-fixed.feature" to "features/bananas.feature"
    When I run "behat"
    Then it should fail with:
      """
      ..F.............F..............................................

      --- Failed steps:

      001 Scenario: I'm little hungry   # features/apples.feature:9
            Then I should have 3 apples # features/apples.feature:11
              Failed asserting that 2 matches expected 3.

      002 Example: | 0   | 4     | 8      | # features/apples.feature:29
            Then I should have 8 apples     # features/apples.feature:24
              Failed asserting that 7 matches expected 8.

      18 scenarios (16 passed, 2 failed)
      63 steps (61 passed, 2 failed)
      """
    When I run "behat --rerun"
    Then it should fail with:
      """
      ..F...F

      --- Failed steps:

      001 Scenario: I'm little hungry   # features/apples.feature:9
            Then I should have 3 apples # features/apples.feature:11
              Failed asserting that 2 matches expected 3.

      002 Example: | 0   | 4     | 8      | # features/apples.feature:29
            Then I should have 8 apples     # features/apples.feature:24
              Failed asserting that 7 matches expected 8.

      2 scenarios (2 failed)
    7 steps (5 passed, 2 failed)
    """

  Scenario: Rerun only suite failed scenarios from suite2 suite
    When I run "behat --suite suite2"
    Then it should fail with:
      """
      ..F.............F....
      
      --- Failed steps:
      
      001 Scenario: I'm little hungry    # features/bananas.feature:9
            Then I should have 3 bananas # features/bananas.feature:11
              Failed asserting that 2 matches expected 3.
      
      002 Example: | 0   | 4     | 8      | # features/bananas.feature:29
            Then I should have 8 bananas    # features/bananas.feature:24
              Failed asserting that 7 matches expected 8.
      
      6 scenarios (4 passed, 2 failed)
      21 steps (19 passed, 2 failed)
      """
    When I run "behat --suite suite2 --rerun"
    And it should fail with:
      """
      ..F...F

      --- Failed steps:

      001 Scenario: I'm little hungry    # features/bananas.feature:9
            Then I should have 3 bananas # features/bananas.feature:11
              Failed asserting that 2 matches expected 3.

      002 Example: | 0   | 4     | 8      | # features/bananas.feature:29
            Then I should have 8 bananas    # features/bananas.feature:24
              Failed asserting that 7 matches expected 8.

      2 scenarios (2 failed)
      7 steps (5 passed, 2 failed)
      """
