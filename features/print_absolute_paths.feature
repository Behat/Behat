Feature: Print absolute paths
  In order to be able to find the right paths to files
  As a developer
  I need to be able to ask Behat to print the full absolute path for files

  Background:
    Given I initialise the working directory from the "PrintAbsolutePaths" fixtures folder
    And I provide the following options for all behat invocations:
      | option          | value            |
      | --no-colors     |                  |

  Scenario: Add option in command line
    When I run behat with the following additional options:
      | option                 | value |
      | --print-absolute-paths |       |
    Then the output should contain:
      """
        Scenario:                                    # %%WORKING_DIR%%features/test.feature:3
          Given I have a passing step                # FeatureContext::iHaveAPassingStep()
          And I have a step that throws an exception # FeatureContext::iHaveAFailingStep()
            Warning: Undefined variable $b in %%WORKING_DIR%%features/bootstrap/FeatureContext.php line 16

      --- Failed scenarios:

          %%WORKING_DIR%%features/test.feature:3
      """

  Scenario: Add option in config file
    When I run behat with the following additional options:
      | option    | value                |
      | --profile | absolute_paths       |
    Then the output should contain:
      """
        Scenario:                                    # %%WORKING_DIR%%features/test.feature:3
          Given I have a passing step                # FeatureContext::iHaveAPassingStep()
          And I have a step that throws an exception # FeatureContext::iHaveAFailingStep()
            Warning: Undefined variable $b in %%WORKING_DIR%%features/bootstrap/FeatureContext.php line 16

      --- Failed scenarios:

          %%WORKING_DIR%%features/test.feature:3
      """
