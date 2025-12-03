Feature: Math
  Scenario Outline:
    When I add <add>
    Then the result should be <result>

    Examples:
      | add | result |
      | 12  | 12     |
      | 3   | 3      |
      | 5   | 5      |
