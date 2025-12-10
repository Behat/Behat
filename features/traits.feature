Feature: Support traits
  In order to have much cleaner horizontal reusability
  As a context developer
  I need to be able to use definition traits in my context

  Background:
    Given I initialise the working directory from the "Traits" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value |
      | --no-colors |       |

  Scenario: Run feature with a context that uses a trait
    When I run "behat --format progress"
    Then it should pass with:
      """
      .....................

      6 scenarios (6 passed)
      21 steps (21 passed)
      """
