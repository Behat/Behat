Feature: Missing Step Feature
  In order to test the stop-on-failure and strict features
  As a behat developer
  I need to have a feature with a missing step

  Background:
    Given I have a step that passes

  Scenario: 1st Failing
    When I have a step that is missing
    Then I should have a scenario that failed

  Scenario: 1st Passing
    When I have a step that passes
    Then I should have a scenario that passed
