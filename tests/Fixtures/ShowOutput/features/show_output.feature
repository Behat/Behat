Feature: Steps with output
  In order to test the show output feature
  As a behat developer
  I need to have some steps that have some output

  Scenario: Some steps with output
    When I have a step that has no output and passes
    And I have a step that shows some output and passes
    And I have a step that shows some output and fails
