Feature: Apples story
  In order to eat apple
  As a little kid
  I need to have an apple in my pocket

  Background:
    Given I have 3 apples

  Scenario: I'm little hungry
    When I ate 1 apple
    Then I should have 2 apples

  Scenario: Found more apples
    When I found 2 apples
    Then I should have 5 apples

  Scenario Outline: Other situations
    When I ate <ate> apples
    And I found <found> apples
    Then I should have <result> apples

    Examples:
      | ate | found | result |
      | 3   | 1     | 1      |
      | 0   | 5     | 8      |
      | 2   | 2     | 3      |
