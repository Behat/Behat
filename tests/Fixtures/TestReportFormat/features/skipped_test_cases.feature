Feature: Skipped test cases
  In order to see all information
  As a features developer
  I want to see skipped test cases in JUnit reports

  Background:
    Given I have entered 10

  @setup-error
  Scenario: Skipped
    Then I must have 10

  @setup-error
  Scenario: Another skipped
    Then I must have 10

