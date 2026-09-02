Feature: Bananas story
  In order to eat bananas
  As a little kid
  I need to have bananas in my pocket

  Background:
    Given I have 3 bananas

  Scenario: I'm little hungry
    When I ate 1 bananas
    Then I should have 3 bananas
