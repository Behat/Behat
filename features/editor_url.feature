Feature: Editor URL
  In order to be able to open files directly in my editor
  As a developer
  I need to be able to ask Behat to add editor links to file paths in the output

  Background:
    Given I initialise the working directory from the "EditorUrl" fixtures folder
    And I provide the following options for all behat invocations:
      | option          | value            |
      | --no-colors     |                  |

  Scenario: Add option in command line
    When I run behat with the following additional options:
      | option       | value                                        |
      | --editor-url | 'phpstorm://open?file={relPath}&line={line}' |
    Then the output should contain:
      """
        Scenario:                                    # <href=phpstorm://open?file=features/test.feature&line=3>features/test.feature:3</>
          Given I have a passing step                # FeatureContext::iHaveAPassingStep()
          And I have a step that throws an exception # FeatureContext::iHaveAFailingStep()
            Warning: Undefined variable $b in <href=phpstorm://open?file=features/bootstrap/FeatureContext.php&line=XX>features/bootstrap/FeatureContext.php line XX</>

      --- Failed scenarios:

          <href=phpstorm://open?file=features/test.feature&line=3>features/test.feature:3</>
      """

  Scenario: Add option in config file
    When I run behat with the following additional options:
      | option    | value      |
      | --profile | editor_url |
    Then the output should contain:
      """
        Scenario:                                    # <href=phpstorm://open?file=features/test.feature&line=3>features/test.feature:3</>
          Given I have a passing step                # FeatureContext::iHaveAPassingStep()
          And I have a step that throws an exception # FeatureContext::iHaveAFailingStep()
            Warning: Undefined variable $b in <href=phpstorm://open?file=features/bootstrap/FeatureContext.php&line=XX>features/bootstrap/FeatureContext.php line XX</>

      --- Failed scenarios:

          <href=phpstorm://open?file=features/test.feature&line=3>features/test.feature:3</>
      """

  Scenario: Use absolute paths in editor URL
    When I run behat with the following additional options:
      | option       | value                                        |
      | --editor-url | 'phpstorm://open?file={absPath}&line={line}' |
    Then the output should contain:
      """
        Scenario:                                    # <href=phpstorm://open?file=%%WORKING_DIR%%features/test.feature&line=3>features/test.feature:3</>
          Given I have a passing step                # FeatureContext::iHaveAPassingStep()
          And I have a step that throws an exception # FeatureContext::iHaveAFailingStep()
            Warning: Undefined variable $b in <href=phpstorm://open?file=%%WORKING_DIR%%features/bootstrap/FeatureContext.php&line=XX>features/bootstrap/FeatureContext.php line XX</>

      --- Failed scenarios:

          <href=phpstorm://open?file=%%WORKING_DIR%%features/test.feature&line=3>features/test.feature:3</>
      """

  Scenario: Use relative paths in url but absolute paths in visible text
    When I run behat with the following additional options:
      | option                 | value                                        |
      | --print-absolute-paths |                                              |
      | --editor-url           | 'phpstorm://open?file={relPath}&line={line}' |
    Then the output should contain:
      """
        Scenario:                                    # <href=phpstorm://open?file=features/test.feature&line=3>%%WORKING_DIR%%features/test.feature:3</>
          Given I have a passing step                # FeatureContext::iHaveAPassingStep()
          And I have a step that throws an exception # FeatureContext::iHaveAFailingStep()
            Warning: Undefined variable $b in <href=phpstorm://open?file=features/bootstrap/FeatureContext.php&line=XX>%%WORKING_DIR%%features/bootstrap/FeatureContext.php line XX</>

      --- Failed scenarios:

          <href=phpstorm://open?file=features/test.feature&line=3>%%WORKING_DIR%%features/test.feature:3</>
      """

  Scenario: Editor URL does not affect JSON formatter paths
    When I run behat with the following additional options:
      | option    | value                                        |
      | --profile | editor_url                                   |
      | --format  | json                                         |
      | --out     | report.json                                  |
    Then the "report.json" file json should be like:
      """
      {
          "tests": 1,
          "skipped": 0,
          "failed": 1,
          "pending": 0,
          "undefined": 0,
          "suites": [
              {
                  "name": "default",
                  "tests": 1,
                  "skipped": 0,
                  "failed": 1,
                  "pending": 0,
                  "undefined": 0,
                  "features": [
                      {
                          "name": "",
                          "file": "features-DIRECTORY-SEPARATOR-test.feature",
                          "tests": 1,
                          "skipped": 0,
                          "failed": 1,
                          "pending": 0,
                          "undefined": 0,
                          "scenarios": [
                              {
                                  "name": "",
                                  "status": "failed",
                                  "file": "features-DIRECTORY-SEPARATOR-test.feature",
                                  "line": 5,
                                  "failures": [
                                      {
                                          "message": "And I have a step that throws an exception: Warning: Undefined variable $b in features-DIRECTORY-SEPARATOR-bootstrap-DIRECTORY-SEPARATOR-FeatureContext.php line XX",
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

  Scenario: Editor URL does not affect JUnit formatter paths
    When I run behat with the following additional options:
      | option    | value                                      |
      | --profile | editor_url                                 |
      | --format  | junit                                      |
      | --out     | report                                     |
    Then the "report/default.xml" file xml should be like:
      """
      <?xml version="1.0" encoding="UTF-8"?>
      <testsuites name="default">
        <testsuite name="" file="features-DIRECTORY-SEPARATOR-test.feature" tests="1" skipped="0" failures="1" errors="0">
          <testcase name="" classname="" status="failed" file="features-DIRECTORY-SEPARATOR-test.feature" line="5">
            <failure message="And I have a step that throws an exception: Warning: Undefined variable $b in features-DIRECTORY-SEPARATOR-bootstrap-DIRECTORY-SEPARATOR-FeatureContext.php line XX"></failure>
          </testcase>
        </testsuite>
      </testsuites>
      """
