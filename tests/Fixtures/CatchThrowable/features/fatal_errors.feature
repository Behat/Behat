Feature: Fatal error in scenario
  In order to test the handling of the PHP 7 Throwable interface
  As a contributor of Behat
  I need to have a FeatureContext that contains errors that were fatal in previous PHP versions

  Scenario: Handling of a fatal error
    When I have some code with a fatal error
    Then I should be skipped
