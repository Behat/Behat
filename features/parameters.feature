Feature: Parameters
  In order to support different setups
  As a tester
  I need to be able to configure Behat through environment variable

  Background:
    Given I initialise the working directory from the "Parameters" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value |
      | --no-colors |       |

  Scenario:
    When I run "behat --config=behat-progress.php"
    Then it should pass with:
      """
      ...............

      3 scenarios (3 passed)
      15 steps (15 passed)
      """

  Scenario:
    Given "BEHAT_PARAMS" environment variable is set to:
      """
      {"formatters": {"pretty": {"paths": false, "timer": false}}}
      """
    When I run "behat --config=behat-empty-formatters.php"
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

  Scenario:
    Given "BEHAT_PARAMS" environment variable is set to:
      """
      {"formatters": {"pretty": {"timer": false}}}
      """
    When I run "behat --config=behat-empty-formatters.php"
    Then it should pass with:
      """
      Feature: Math

        Background:                     # features/math.feature:2
          Given I have basic calculator # FeatureContext::iHaveBasicCalculator()

        Scenario Outline:                    # features/math.feature:5
          Given I have entered <number1>     # FeatureContext::iHaveEntered()
          And I have entered <number2>       # FeatureContext::iHaveEntered()
          When I add                         # FeatureContext::iAdd()
          Then The result should be <result> # FeatureContext::theResultShouldBe()

          Examples:
            | number1 | number2 | result |
            | 10      | 12      | 22     |
            | 5       | 3       | 8      |
            | 5       | 5       | 10     |

      3 scenarios (3 passed)
      15 steps (15 passed)
      """
