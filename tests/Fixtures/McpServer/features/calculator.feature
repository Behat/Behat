Feature: Calculator operations
  In order to perform calculations
  As a user
  I need to be able to use basic math operations

  Scenario: Addition
    Given I have the number 5
    When I add 3
    Then the result should be 8

  Scenario: Subtraction
    Given I have the number 10
    When I subtract 4
    Then the result should be 6

  Scenario: Multiplication
    Given I have the number 7
    When I multiply by 3
    Then the result should be 21
