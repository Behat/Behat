Feature: Fruit story
  In order to eat fruit
  As a little kid
  I need to have fruit in my pocket

  Scenario: I'm little hungry for apples
    Given I have 3 apples
    When I eat 1 apple
    Then I should have 2 apples

  Scenario: I'm little hungry for bananas
    Given I have 3 bananas
    When I eat 1 banana
    Then I should have 2 bananas
