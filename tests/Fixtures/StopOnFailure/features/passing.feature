Feature: Passing Feature
  In order to test the stop-on-failure feature
  As a behat developer
  I need to have a feature that passes

  Background:
    Given I have a step that passes

  Scenario: 1st Passing
    When I have a step that passes
    Then I should have a scenario that passed

  Scenario: 2nd Passing
    When I have a step that passes
    And I have another step that passes
    Then I should have a scenario that passed

  Scenario: 3rd Passing
    When I have a step that passes
    And I have another step that passes
    And I have another step that passes
    Then I should have a scenario that passed
