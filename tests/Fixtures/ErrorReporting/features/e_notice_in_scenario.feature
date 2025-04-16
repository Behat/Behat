Feature: E_NOTICE in scenario
  In order to test the BEHAT_ERROR_REPORTING constant
  As a contributor of behat
  I need to have a FeatureContext that throws E_NOTICE within steps.

  Background:
    Given I have an empty array

  Scenario: Access undefined index
    When I access array index 0
    Then I should get NULL

  Scenario: Access defined index
    When I push "foo" to that array
    And I access array index 0
    Then I should get "foo"
