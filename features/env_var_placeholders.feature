Feature: Symfony Env Var Placeholders
  In order to support different setups
  As a tester
  I need to be able to use environment variables in the behat.yml configuration file

  Background:
    Given I initialise the working directory from the "EnvVarPlaceholders" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value |
      | --no-colors |       |

  Scenario:
    When the "MY_ENV_VAR" environment variable is set to "some environment variable value"
    And I run "behat"
    Then it should pass with:
      """
      1 scenario (1 passed)
      1 step (1 passed)
      """
