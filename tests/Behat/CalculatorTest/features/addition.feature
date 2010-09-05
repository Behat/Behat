# language: en
Feature: Addition
  In order to avoid silly mistakes
  As a math idiot 
  I want to be told the sum of two numbers

  Scenario Outline: Add two numbers
    Given I have entered <first> into the calculator
    And I have entered <second> into the calculator
    When I press add
    Then the result should be <result> on the screen

    Examples:
    | first | second | result |
    |   3   |   5    |   8    |
    |   10  |   12   |   22   |
    |   13  |   5    |   18   |
    |   15  |   2    |   17   |
