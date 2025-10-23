Feature: Remove prefix
  In order to have cleaner output with shorter paths
  As a developer
  I need to be able to ask Behat to remove specific prefixes from paths in the output

  Background:
    Given I initialise the working directory from the "RemovePrefix" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value |
      | --no-colors |       |

  Scenario: Add option in command line
    When I run behat with the following additional options:
      | option          | value                         |
      | --remove-prefix | features/bootstrap/,features/ |
    Then the output should contain:
      """
        Scenario:                                    # test.feature:3
          Given I have a passing step                # FeatureContext::iHaveAPassingStep()
          And I have a step that throws an exception # FeatureContext::iHaveAFailingStep()
            Warning: Undefined variable $b in FeatureContext.php line 16

      --- Failed scenarios:

          test.feature:3
      """

  Scenario: Add option in config file
    When I run behat with the following additional options:
      | option    | value         |
      | --profile | remove_prefix |
    Then the output should contain:
      """
        Scenario:                                    # test.feature:3
          Given I have a passing step                # FeatureContext::iHaveAPassingStep()
          And I have a step that throws an exception # FeatureContext::iHaveAFailingStep()
            Warning: Undefined variable $b in FeatureContext.php line 16

      --- Failed scenarios:

          test.feature:3
      """

  Scenario: Use remove prefix with editor URL
    When I run behat with the following additional options:
      | option          | value                                        |
      | --editor-url    | 'phpstorm://open?file={relPath}&line={line}' |
      | --remove-prefix | features/bootstrap/,features/                |
    Then the output should contain:
      """
        Scenario:                                    # <href=phpstorm://open?file=features/test.feature&line=3>test.feature:3</>
          Given I have a passing step                # FeatureContext::iHaveAPassingStep()
          And I have a step that throws an exception # FeatureContext::iHaveAFailingStep()
            Warning: Undefined variable $b in <href=phpstorm://open?file=features/bootstrap/FeatureContext.php&line=16>FeatureContext.php line 16</>

      --- Failed scenarios:

          <href=phpstorm://open?file=features/test.feature&line=3>test.feature:3</>
      """

  Scenario: Use remove prefix with absolute paths
    When I run behat with the following additional options:
      | option                 | value       |
      | --print-absolute-paths |             |
      | --remove-prefix        | {BASE_PATH} |
    Then the output should contain:
      """
        Scenario:                                    # features%%DS%%test.feature:3
          Given I have a passing step                # FeatureContext::iHaveAPassingStep()
          And I have a step that throws an exception # FeatureContext::iHaveAFailingStep()
            Warning: Undefined variable $b in features%%DS%%bootstrap%%DS%%FeatureContext.php line 16

      --- Failed scenarios:

          features%%DS%%test.feature:3
      """

  Scenario: Remove prefixes in JSON formatter
    When I run behat with the following additional options:
      | option    | value           |
      | --profile | remove_prefix   |
      | --suite   | default         |
      | --format  | json            |
      | --out     | report.json     |
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
                                  "file": "test.feature",
                                  "line": 5,
                                  "failures": [
                                      {
                                          "message": "And I have a step that throws an exception: Warning: Undefined variable $b in FeatureContext.php line 16",
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

  Scenario: Remove prefixes in JUnit formatter
    When I run behat with the following additional options:
      | option    | value           |
      | --profile | remove_prefix   |
      | --suite   | default         |
      | --format  | junit           |
      | --out     | report          |
    Then the "report/default.xml" file xml should be like:
      """
      <?xml version="1.0" encoding="UTF-8"?>
      <testsuites name="default">
        <testsuite name="" tests="1" skipped="0" failures="1" errors="0" time="-IGNORE-VALUE-">
          <testcase name="" classname="" status="failed" time="-IGNORE-VALUE-" file="test.feature" line="5">
            <failure message="And I have a step that throws an exception: Warning: Undefined variable $b in FeatureContext.php line 16"></failure>
          </testcase>
        </testsuite>
      </testsuites>
      """
