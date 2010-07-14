# language: en
Feature: Addition
  In order to avoid silly mistakes
  As a math idiot 
  I want to be told the sum of two numbers

  Scenario: Add two numbers
    Given I have entered 11 into the calculator
    And I have entered 12 into the calculator
    When I press add
    Then the result should be 23 on the screen

  Scenario: Div two numbers
    Given I have entered 10 into the calculator
    And I have entered 2 into the calculator
    When I press div
    Then the result should be 5 on the screen
