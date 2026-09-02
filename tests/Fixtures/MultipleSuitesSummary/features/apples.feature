Feature: Apples story
  In order to eat apples
  As a little kid
  I need to have apples in my pocket

  Background:
    Given I have 3 apples

  Scenario: I'm little hungry
    When I ate 1 apples
    Then I should have 3 apples

  Scenario: Found more apples
    When I found 5 apples
    Then I should have 8 apples

  Scenario Outline: Other situations
    When I ate <ate> apples
    Then I should have <result> apples

    Examples:
      | ate | result |
      | 1   | 2      |
      | 1   | 8      |
