# language: martian
Feature: Gherkin compatibility mode
  In order to have my feature files parsed as they would be by other cucumber-based tools
  As a tester
  I need to be able to configure the Gherkin parser compatibility mode

  Scenario: Enforce language validation
      Given I have a feature file like this with an invalid language tag
      When  I run Behat
      Then  it should pass in legacy mode and fail in gherkin-32 compatibility mode
