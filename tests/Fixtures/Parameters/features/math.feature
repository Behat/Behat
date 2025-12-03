Feature: Math
  Background:
    Given I have basic calculator

  Scenario Outline:
    Given I have entered <number1>
    And I have entered <number2>
    When I add
    Then The result should be <result>

    Examples:
      | number1 | number2 | result |
      | 10      | 12      | 22     |
      | 5       | 3       | 8      |
      | 5       | 5       | 10     |
