Feature: Adding numbers
  In order to add numbers together
  As a mathematician
  I want something that acts like a calculator

  Background:
    Given I have entered 10

  Scenario: Passed
    When I add 4
    Then I must have 14

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

  Scenario Outline: Passed & Failed with value=<value>
    When I add <value>
    Then I must have <result>

    Examples:
      | value | result |
      |  5    | 16     |
      |  10   | 20     |
      |  23   | 32     |

  Scenario Outline: Another Outline (<value> = <result>)
    When I add <value>
    Then I must have <result>

    Examples:
      | value | result |
      | 5     | 15     |
      | 10    | 20     |
