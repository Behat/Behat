Feature: Failing Feature
  In order to test the stop-on-failure feature
  As a behat developer
  I need to have a feature that fails

  Background:
    Given I have a step that passes

  Scenario: 1st Passing
    When I have a step that passes
    Then I should have a scenario that passed

  Scenario: 1st Failing
    When I have a step that passes
    And I have another step that fails
    Then I should have a scenario that failed

  Scenario: 2nd Failing
    When I have a step that fails
    Then I should have a scenario that failed

  Scenario: 2nd Passing
    When I have a step that passes
    And I have another step that passes
    Then I should have a scenario that passed
