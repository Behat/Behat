Feature: Division
  In order to avoid silly mistakes
  As a math idiot 
  I want to be told the sum of two numbers

  Scenario: Div two numbers
    Given I have entered 18 into the calculator
    And I have entered 3 into the calculator
    When I press div
    Then the result should be 6 on the screen
