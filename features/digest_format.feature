Feature: Digest Formatter
  In order to have an overview of the project features
  As a feature writer
  I need to have digest formatter

  Scenario: Complex
    Given a file named "features/World.feature" with:
      """
      Feature: World consistency
        In order to maintain stable behaviors
        As a features developer
        I want, that "World" flushes between scenarios

        Background:
          Given I have entered 10

        Scenario: Undefined
          Then I must have 10
          And Something new
          Then I must have 10

        Scenario: Pending
          Then I must have 10
          And Something not done yet
          Then I must have 10

        Scenario: Failed
          When I add 4
          Then I must have 13

        Scenario Outline: Passed & Failed
          Given I must have 10
          When I add <value>
          Then I must have <result>

          Examples:
            | value | result |
            |  5    | 16     |
            |  10   | 20     |
            |  23   | 32     |
      """
    When I run "behat --no-colors -f digest"
    Then it should pass with:
      """
      Feature: World consistency
        In order to maintain stable behaviors
        As a features developer
        I want, that "World" flushes between scenarios

        Scenario: Undefined       # features/World.feature:9
        Scenario: Pending         # features/World.feature:14
        Scenario: Failed          # features/World.feature:19
        Scenario: Passed & Failed # features/World.feature:23
      """
