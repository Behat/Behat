Feature: Adding easy numbers
  In order to add numbers together
  As a small kid
  I want something that acts like a calculator for easy sums

  Background:
    Given I have entered 1

  Scenario: Easy sum
    When I add 2
    Then I must have 3
