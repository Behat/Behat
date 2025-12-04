Feature: World consistency
  In order to maintain stable behaviors
  As a features developer
  I want, that "World" flushes between scenarios

  Background: Some background
    title
      with
  multiple lines

    Given I have entered 10

  Scenario: Undefined
            scenario or
            whatever
    Then I must have 10
    And Something new
    Then I must have 10

Scenario Outline: Passed & Failed
steps and other interesting stuff
  he-he-he

    Given I must have 10
    When I add <value>
    Then I must have <result>

    Examples:
      | value | result |
      |  5    | 16     |
      |  10   | 20     |
      |  23   | 32     |
