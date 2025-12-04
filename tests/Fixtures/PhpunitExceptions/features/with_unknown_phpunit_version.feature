@phpunit_incompatible
Feature: Values do not match
  In order to test the stringification of PHPUnit assertions
  As a contributor of behat
  I need to have a scenario that demonstrates failing assertions

  Scenario: Compare mismatched array
    Then an array {"value": "foo"} should equal {"value": "bar"}

  Scenario: Compare matching array
    Then an array {"value": "foo"} should equal {"value": "foo"}

  Scenario: Compare mismatched ints
    Then an integer 1 should equal 2

  Scenario: Compare matching ints
    Then an integer 1 should equal 1
