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
        Scenario:                                    # %%WORKING_DIR%%features%%DS%%test.feature:3
          Given I have a passing step                # FeatureContext::iHaveAPassingStep()
          And I have a step that throws an exception # FeatureContext::iHaveAFailingStep()
            Warning: Undefined variable $b in %%WORKING_DIR%%features%%DS%%bootstrap%%DS%%FeatureContext.php line 16

      --- Failed scenarios:

          %%WORKING_DIR%%features%%DS%%test.feature:3
      """

  Scenario: Add option in config file
    When I run behat with the following additional options:
      | option    | value                |
      | --profile | absolute_paths       |
    Then the output should contain:
      """
        Scenario:                                    # %%WORKING_DIR%%features%%DS%%test.feature:3
          Given I have a passing step                # FeatureContext::iHaveAPassingStep()
          And I have a step that throws an exception # FeatureContext::iHaveAFailingStep()
            Warning: Undefined variable $b in %%WORKING_DIR%%features%%DS%%bootstrap%%DS%%FeatureContext.php line 16

      --- Failed scenarios:

          %%WORKING_DIR%%features%%DS%%test.feature:3
      """

  Scenario: Print absolute paths in JSON formatter
    When I run behat with the following additional options:
      | option    | value          |
      | --profile | absolute_paths |
      | --format  | json           |
      | --out     | report.json    |
    Then the "report.json" file json should be like:
      """
      {
          "tests": 1,
          "skipped": 0,
          "failed": 1,
          "pending": 0,
          "undefined": 0,
          "time": -IGNORE-VALUE-,
          "suites": [
              {
                  "name": "default",
                  "tests": 1,
                  "skipped": 0,
                  "failed": 1,
                  "pending": 0,
                  "undefined": 0,
                  "time": -IGNORE-VALUE-,
                  "features": [
                      {
                          "name": "",
                          "tests": 1,
                          "skipped": 0,
                          "failed": 1,
                          "pending": 0,
                          "undefined": 0,
                          "time": -IGNORE-VALUE-,
                          "scenarios": [
                              {
                                  "name": "",
                                  "time": -IGNORE-VALUE-,
                                  "status": "failed",
                                  "file": "%%WORKING_DIR%%features-DIRECTORY-SEPARATOR-test.feature",
                                  "line": 5,
                                  "failures": [
                                      {
                                          "message": "And I have a step that throws an exception: Warning: Undefined variable $b in %%WORKING_DIR%%features-DIRECTORY-SEPARATOR-bootstrap-DIRECTORY-SEPARATOR-FeatureContext.php line 16",
                                          "type": "failed"
                                      }
                                  ]
                              }
                          ]
                      }
                  ]
              }
          ]
      }
      """

  Scenario: Print absolute paths in JUnit formatter
    When I run behat with the following additional options:
      | option    | value          |
      | --profile | absolute_paths |
      | --format  | junit          |
      | --out     | report         |
    Then the "report/default.xml" file xml should be like:
      """
      <?xml version="1.0" encoding="UTF-8"?>
      <testsuites name="default">
        <testsuite name="" tests="1" skipped="0" failures="1" errors="0" time="-IGNORE-VALUE-">
          <testcase name="" classname="" status="failed" time="-IGNORE-VALUE-" file="%%WORKING_DIR%%features-DIRECTORY-SEPARATOR-test.feature" line="5">
            <failure message="And I have a step that throws an exception: Warning: Undefined variable $b in %%WORKING_DIR%%features-DIRECTORY-SEPARATOR-bootstrap-DIRECTORY-SEPARATOR-FeatureContext.php line 16"></failure>
          </testcase>
        </testsuite>
      </testsuites>
      """
