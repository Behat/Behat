Feature: Error Reporting
  In order to ignore or detect PHP warnings in code I depend upon
  As a feature developer
  I need to have an ability to set a custom error level for steps to be executed in

  Background:
    Given I initialise the working directory from the "ErrorReporting" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value    |
      | --no-colors |          |
      | --format    | progress |

  Scenario: With error reporting including more errors than php option
    Given I set the php error_reporting option for the behat command to "none"
    When I run behat with the following additional options:
      | option                                  | value             |
      | --profile                               | do-not-ignore-any |
      | features/php_errors_in_scenario.feature |                   |
    Then it should fail with:
    """
    --- Failed steps:

    001 Scenario: Access undefined index # features/php_errors_in_scenario.feature:9
          When I access array index 0    # features/php_errors_in_scenario.feature:10
            Warning: Undefined array key 0 in features/bootstrap/FeatureContext.php line XX

    002 Scenario: Trigger PHP deprecation # features/php_errors_in_scenario.feature:18
          When I trim NULL                # features/php_errors_in_scenario.feature:19
            Deprecated: trim(): Passing null to parameter #1 ($string) of type string is deprecated in features/bootstrap/FeatureContext.php line XX

    3 scenarios (1 passed, 2 failed)
    9 steps (6 passed, 2 failed, 1 skipped)
    """

  Scenario: With error reporting including less errors than php option
    Given I set the php error_reporting option for the behat command to "all"
    When I run behat with the following additional options:
      | option                                  | value                |
      | --profile                               | ignore-all-but-error |
      | features/php_errors_in_scenario.feature |                      |
    Then it should pass with:
    """
    .......

    3 scenarios (3 passed)
    9 steps (9 passed)
    """

  Scenario: With error reporting not set in config
    Given I set the php error_reporting option for the behat command to "ignore deprecations"
    When I run behat with the following additional options:
      | option                                  | value |
      | features/php_errors_in_scenario.feature |       |
    Then it should fail with:
    """
    --- Failed steps:

    001 Scenario: Access undefined index # features/php_errors_in_scenario.feature:9
          When I access array index 0    # features/php_errors_in_scenario.feature:10
            Warning: Undefined array key 0 in features/bootstrap/FeatureContext.php line XX

    3 scenarios (2 passed, 1 failed)
    9 steps (7 passed, 1 failed, 1 skipped)
    """

  Scenario: With error reporting set in yaml config
    Given I set the php error_reporting option for the behat command to "none"
    When I run behat with the following additional options:
      | option                                  | value          |
      | --config                                | all_errors.yml |
      | features/php_errors_in_scenario.feature |                |
    Then it should fail with:
    """
    --- Failed steps:

    001 Scenario: Access undefined index # features/php_errors_in_scenario.feature:9
          When I access array index 0    # features/php_errors_in_scenario.feature:10
            Warning: Undefined array key 0 in features/bootstrap/FeatureContext.php line XX

    002 Scenario: Trigger PHP deprecation # features/php_errors_in_scenario.feature:18
          When I trim NULL                # features/php_errors_in_scenario.feature:19
            Deprecated: trim(): Passing null to parameter #1 ($string) of type string is deprecated in features/bootstrap/FeatureContext.php line XX

    3 scenarios (1 passed, 2 failed)
    9 steps (6 passed, 2 failed, 1 skipped)
    """

  Scenario: With very verbose error reporting
    Given I set the php error_reporting option for the behat command to "all"
    When I run behat with the following additional options:
      | option                                 | value   |
      | --profile                              | default |
      | -vv                                    |         |
      | features/exception_in_scenario.feature |         |
    Then it should fail with:
    """
    --- Failed steps:

    001 Scenario: Exception thrown    # features/exception_in_scenario.feature:6
          When an exception is thrown # features/exception_in_scenario.feature:7
            Exception: Exception is thrown in features/bootstrap/FeatureContext.php:XX
            Stack trace:

    1 scenario (1 failed)
    1 step (1 failed)
    """

  Scenario: With debug verbose error reporting
    Given I set the php error_reporting option for the behat command to "all"
    When I run behat with the following additional options:
      | option                                 | value   |
      | --profile                              | default |
      | -vvv                                   |         |
      | features/exception_in_scenario.feature |         |
    Then it should fail with:
    """
    --- Failed steps:

    001 Scenario: Exception thrown    # features/exception_in_scenario.feature:6
          When an exception is thrown # features/exception_in_scenario.feature:7
            Exception: Exception is thrown in features/bootstrap/FeatureContext.php:XX
            Stack trace:
            #0 {BASE_PATH}src/Behat/Testwork/Call/Handler/RuntimeCallHandler.php(XX): FeatureContext->anExceptionIsThrown()
            #1 {BASE_PATH}src/Behat/Testwork/Call/Handler/RuntimeCallHandler.php(XX): Behat\Testwork\Call\Handler\RuntimeCallHandler->executeCall(
    """
