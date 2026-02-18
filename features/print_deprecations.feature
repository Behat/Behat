Feature: Print deprecations
  In order to identify deprecated code usage in Behat
  As a developer
  I need to be able to see deprecation warnings triggered by Behat code

  Background:
    Given I initialise the working directory from the "Deprecations" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value    |
      | --no-colors |          |
      | --format    | progress |

  Scenario: Do not print deprecations by default
    When I run "behat"
    Then it should pass with:
      """
      ..

      1 scenario (1 passed)
      2 steps (2 passed)
      """

  Scenario: Print deprecations using config option
    When I run behat with the following additional options:
      | option    | value              |
      | --profile | print_deprecations |
    Then it should pass with:
      """
      2 deprecations triggered (2 unique):

        ⚠ This extension triggers a deprecation during initialization
        ⚠ This extension triggers a deprecation during load
      """

  Scenario: Print deprecations using CLI option
    When I run behat with the following additional options:
      | option               | value |
      | --print-deprecations |       |
    Then it should pass with:
      """
      2 deprecations triggered (2 unique):

        ⚠ This extension triggers a deprecation during initialization
        ⚠ This extension triggers a deprecation during load
      """

  Scenario: Deprecations in step definitions and hooks are not printed
    When I run behat with the following additional options:
      | option   | value                   |
      | --config | behat-no-extension.php  |
    Then it should pass with:
      """
      ..

      1 scenario (1 passed)
      2 steps (2 passed)
      """
