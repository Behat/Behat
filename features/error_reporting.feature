Feature: Error Reporting
  In order to ignore or detect PHP warnings in code I depend upon
  As a feature developer
  I need to have an ability to set a custom error level for steps to be executed in

  Background:
    Given I set the working directory to the "ErrorReporting" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value    |
      | --no-colors |          |
      | --format    | progress |


  Scenario: With default error reporting reports notices
    When I run behat with the following additional options:
      | option                                | value   |
      | --profile                             | default |
      | features/e_notice_in_scenario.feature |         |
    Then it should fail with:
    """
    --- Failed steps:

    001 Scenario: Access undefined index # features/e_notice_in_scenario.feature:9
          When I access array index 0    # features/e_notice_in_scenario.feature:10
            Notice: Undefined offset: 0 in features/bootstrap/FeatureContext.php line 24

    2 scenarios (1 passed, 1 failed)
    7 steps (5 passed, 1 failed, 1 skipped)
    """

  Scenario: With error reporting ignoring E_NOTICE and E_WARNING
    When I run behat with the following additional options:
      | option                                | value                     |
      | --profile                             | ignore-notice-and-warning |
      | features/e_notice_in_scenario.feature |                           |
    Then it should pass with:
    """
    .......

    2 scenarios (2 passed)
    7 steps (7 passed)
    """

  Scenario: With very verbose error reporting
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
            Exception: Exception is thrown in features/bootstrap/FeatureContext.php:48
            Stack trace:

    1 scenario (1 failed)
    1 step (1 failed)
    """

  Scenario: With debug verbose error reporting
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
            Exception: Exception is thrown in features/bootstrap/FeatureContext.php:48
            Stack trace:
            #0 {BASE_PATH}src/Behat/Testwork/Call/Handler/RuntimeCallHandler.php(110): FeatureContext->anExceptionIsThrown()
            #1 {BASE_PATH}src/Behat/Testwork/Call/Handler/RuntimeCallHandler.php(64): Behat\Testwork\Call\Handler\RuntimeCallHandler->executeCall(
    """
