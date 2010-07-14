Feature: World consistency
  In order to maintain stable behaviors
  As a features developer
  I want, that "World" flushes between scenarios

  Background:
    Given I have entered 10

  Scenario:
    Then I must have 10

  Scenario:
    When I add 3
    Then I must have 13

  Scenario Outline:
    When I add <value>
    Then I must have <result>

    Examples:
      | value | result |
      |  5    | 15     |
      |  10   | 20     |
      |  23   | 33     |
