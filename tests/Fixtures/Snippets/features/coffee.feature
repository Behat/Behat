Feature: Snippets

  Background:
    Given I have magically created 10$

  Scenario: Single quotes
    When I have chosen 'coffee with turkey' in coffee machine
    Then I should have 'turkey with coffee sauce'
    And I should get a 'super/string':
      """
      Test #1
      """
    And I should get a simple string:
      """
      Test #2
      """

  Scenario: Double quotes
    When I have chosen "pizza tea" in coffee machine
    And do something undefined with \1
    Then I should have "pizza tea"
    And I should get a "super/string":
      """
      Test #1
      """
    And I should get a simple string:
      """
      Test #2
      """
