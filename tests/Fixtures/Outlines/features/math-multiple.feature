Feature: Math
  Background:
    Given I have basic calculator

  Scenario Outline:
    Given I have entered <number1>
    And I have entered <number2>
    When I multiply
    Then The result should be <result>

    Examples:
      | number1 | number2 | result |
      | 10      | 12      | 120    |
      | 5       | 3       | 15     |

  Scenario:
    Given I have entered 10
    And I have entered 3
    When I sub
    Then The result should be 7

  Scenario Outline:
    Given I have entered <number1>
    And I have entered <number2>
    When I div
    Then The result should be <result>

    Examples:
      | number1 | number2 | result |
      | 10      | 2       | 5      |
      | 50      | 5       | 10     |
