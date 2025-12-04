Feature: Preferred Profiles
  In order to test my preferred profile
  As a tester
  I need to be able to specify a preferred profile

  Background:
  Given I initialise the working directory from the "PreferredProfile" fixtures folder
  And I provide the following options for all behat invocations:
    | option      | value |
    | --no-colors |       |

  Scenario:
    When I run "behat features/math.feature"
    Then it should pass with:
      """
      ...............

      3 scenarios (3 passed)
      15 steps (15 passed)
      """

  Scenario:
    When I run "behat features/math.feature --profile progress"
    Then it should pass with:
      """
      ...............

      3 scenarios (3 passed)
      15 steps (15 passed)
      """

  Scenario:
    When I run "behat --profile pretty_without_paths"
    Then it should pass with:
      """
      Feature: Math

        Background:
          Given I have basic calculator

        Scenario Outline:
          Given I have entered <number1>
          And I have entered <number2>
          When I add
          Then The result should be <result>

          Examples:
            | number1 | number2 | result |
            | 10      | 12      | 22     |
            | 5       | 3       | 8      |
            | 5       | 5       | 10     |

      3 scenarios (3 passed)
      15 steps (15 passed)
      """
