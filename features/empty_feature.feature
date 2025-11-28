Feature: Empty feature
  In order to follow BDD practice without a hassle
  As a BDD practitioner
  I need to be able to leave scenario titles without steps for time being

  Background:
    Given I initialise the working directory from the "EmptyFeature" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value    |
      | --no-colors |          |
      | --format    | progress |

  Scenario: Empty scenario
    When I run "behat"
    Then it should pass with:
      """
      No scenarios
      No steps
      """
