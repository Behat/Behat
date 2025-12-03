Feature: Math
  Background:
    Given I have basic calculator

  Scenario Outline:
    Given I have entered <number1>
    And I have entered <number2>
    When I multiply
    Then The result should be <result>

    Examples: Small numbers
      | number1 | number2 | result |
      | 1       | 6       | 6      |
      | 5       | 4       | 10     |
      | 2       | 3       | 6      |

    Examples: Big numbers
      | number1 | number2 | result |
      | 139     | 201     | 99     |
      | 200     | 300     | 60000  |
