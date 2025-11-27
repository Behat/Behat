Feature: Config reference
  In order to know the available configuration
  As a Behat user
  I need to be able to dump the configuration reference

  Background:
    Given I provide the following options for all behat invocations:
      | option      | value |
      | --no-colors |       |

  Scenario: Reference of defaults extension
    When I run "behat --config-reference -v"
    Then it should pass
    And the output should contain:
      """
      suites:
      """
    And the output should contain:
      """
      exceptions:
      """

  Scenario: Custom extension
    Given I initialise the working directory from the "ConfigReference" fixtures folder
    When I run "behat --config-reference"
    Then it should pass
    And the output should contain:
      """
          custom_extension:

              # A child node
              child:                ~
              test:                 true
      """
    And the output should contain:
      """
      # A child node
      """
    And the output should contain:
      """
      test:                 true
      """
