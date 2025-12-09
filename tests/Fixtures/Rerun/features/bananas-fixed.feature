Feature: Banana story
  In order to eat banana
  As a little kid
  I need to have an banana in my pocket

  Background:
    Given I have 3 bananas

  Scenario: I'm little hungry
    When I ate 1 bananas
    Then I should have 2 bananas

  Scenario: Found more bananas
    When I found 5 bananas
    Then I should have 8 bananas

  Scenario: Found more bananas
    When I found 2 bananas
    Then I should have 5 bananas

  Scenario Outline: Other situations
    When I ate <ate> bananas
    And I found <found> bananas
    Then I should have <result> bananas

    Examples:
      | ate | found | result |
      | 3   | 1     | 1      |
      | 0   | 4     | 7      |
      | 2   | 2     | 3      |
