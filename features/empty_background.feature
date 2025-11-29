Feature: Empty background
  In order to follow BDD practice without a hassle
  As a BDD practitioner
  I need to be able to leave background definition without steps

  Background:
    Given I initialise the working directory from the "EmptyBackground" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value    |
      | --no-colors |          |
      | --format    | progress |

  Scenario: Empty background
    When I run "behat features/empty_background.feature"
    Then it should pass with:
      """
      .

      1 scenario (1 passed)
      1 step (1 passed)
      """
