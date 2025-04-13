Feature: Print absolute paths
  In order to be able to find the right paths to files
  As a developer
  I need to be able to ask Behat to print the full absolute path for files

  Background:
    Given I set the working directory to the "PrintAbsolutePaths" fixtures folder
    And I provide the following options for all behat invocations:
      | option          | value            |
      | --no-colors     |                  |

  Scenario: Add option in command line
    When I run behat with the following additional options:
      | option                 | value |
      | --print-absolute-paths |       |
    Then the output with absolute paths should contain:
      """
        Scenario:                                    # BASE_PATH/tests/Fixtures/PrintAbsolutePaths/features/test.feature:3
          Given I have a passing step                # FeatureContext::iHaveAPassingStep()
          And I have a step that throws an exception # FeatureContext::iHaveAFailingStep()
            Warning: Undefined variable $b in BASE_PATH/tests/Fixtures/PrintAbsolutePaths/features/bootstrap/FeatureContext.php line 16

      --- Failed scenarios:

          BASE_PATH/tests/Fixtures/PrintAbsolutePaths/features/test.feature:3
      """

  Scenario: Add option in config file
    When I run behat with the following additional options:
      | option    | value                |
      | --profile | absolute_paths       |
    Then the output with absolute paths should contain:
      """
        Scenario:                                    # BASE_PATH/tests/Fixtures/PrintAbsolutePaths/features/test.feature:3
          Given I have a passing step                # FeatureContext::iHaveAPassingStep()
          And I have a step that throws an exception # FeatureContext::iHaveAFailingStep()
            Warning: Undefined variable $b in BASE_PATH/tests/Fixtures/PrintAbsolutePaths/features/bootstrap/FeatureContext.php line 16

      --- Failed scenarios:

          BASE_PATH/tests/Fixtures/PrintAbsolutePaths/features/test.feature:3
      """

  Scenario: Add option in yaml config file
    When I run behat with the following additional options:
      | option    | value               |
      | --config  | absolute_paths.yaml |
    Then the output with absolute paths should contain:
      """
        Scenario:                                    # BASE_PATH/tests/Fixtures/PrintAbsolutePaths/features/test.feature:3
          Given I have a passing step                # FeatureContext::iHaveAPassingStep()
          And I have a step that throws an exception # FeatureContext::iHaveAFailingStep()
            Warning: Undefined variable $b in BASE_PATH/tests/Fixtures/PrintAbsolutePaths/features/bootstrap/FeatureContext.php line 16

      --- Failed scenarios:

          BASE_PATH/tests/Fixtures/PrintAbsolutePaths/features/test.feature:3
      """
