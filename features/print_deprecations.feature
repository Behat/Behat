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
      | --profile | print_behat_deprecations |
    Then it should pass with:
      """
      2 deprecations triggered (2 unique):

        ⚠ This extension triggers a deprecation during initialization
          → deprecation_extension.php:XX
        ⚠ This extension triggers a deprecation during load
          → deprecation_extension.php:XX
      """

  Scenario: Print deprecations using CLI option
    When I run behat with the following additional options:
      | option               | value |
      | --print-behat-deprecations |       |
    Then it should pass with:
      """
      2 deprecations triggered (2 unique):

        ⚠ This extension triggers a deprecation during initialization
          → deprecation_extension.php:XX
        ⚠ This extension triggers a deprecation during load
          → deprecation_extension.php:XX
      """

  Scenario: Print deprecations with absolute paths
    When I run behat with the following additional options:
      | option                   | value |
      | --print-behat-deprecations     |       |
      | --print-absolute-paths   |       |
    Then it should pass with:
      """
      2 deprecations triggered (2 unique):

        ⚠ This extension triggers a deprecation during initialization
          → %%WORKING_DIR%%deprecation_extension.php:XX
        ⚠ This extension triggers a deprecation during load
          → %%WORKING_DIR%%deprecation_extension.php:XX
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

  Scenario: Fail on deprecations passes when no deprecations exist
    When I run behat with the following additional options:
      | option   | value                           |
      | --config | behat-fail-no-deprecations.php  |
    Then it should pass with:
      """
      ..

      1 scenario (1 passed)
      2 steps (2 passed)
      """

  Scenario: Fail on deprecations fails when deprecations exist
    When I run behat with the following additional options:
      | option   | value                            |
      | --config | behat-fail-with-deprecations.php |
    Then it should fail with:
      """
      ..

      1 scenario (1 passed)
      2 steps (2 passed)

      2 deprecations triggered (2 unique):

        ⚠ This extension triggers a deprecation during initialization
          → deprecation_extension.php:XX
        ⚠ This extension triggers a deprecation during load
          → deprecation_extension.php:XX
      """

  Scenario: Deprecations in tested code still fail when error reporting includes deprecations
    When I run behat with the following additional options:
      | option   | value                                  |
      | --config | behat-error-reporting-deprecations.php |
    Then it should fail with:
      """
      --- Failed steps:

      001 Scenario: A scenario with an unsuppressed deprecation          # features/unsuppressed_deprecation.feature:3
            Given I run a step that triggers an unsuppressed deprecation # features/unsuppressed_deprecation.feature:4
              User Deprecated: Deprecation triggered in step definition in features/bootstrap/FeatureContext.php line XX
      """
