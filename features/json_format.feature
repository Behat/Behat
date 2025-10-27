Feature: JSON Formatter
  In order to integrate with other development tools
  As a developer
  I need to be able to generate a report in JSON format

  Background:
    Given I initialise the working directory from the "TestReportFormat" fixtures folder
    And I provide the following options for all behat invocations:
      | option          | value |
      | --no-colors     |       |
      | --snippets-type | regex |
      | --format        | json  |

  Scenario: Run a single feature
    When I run behat with the following additional options:
      | option         | value               |
      | --snippets-for | FeatureContext      |
      | --suite        | single_feature      |
      | --out          | single_feature.json |
    Then it should fail with:
      """
      --- FeatureContext has missing steps. Define them with these snippets:

          #[Then('/^Something new$/')]
          public function somethingNew(): void
          {
              throw new PendingException();
          }
      """
    And the "single_feature.json" file json should be like:
      """
      {
          "tests": 9,
          "skipped": 0,
          "failed": 3,
          "pending": 1,
          "undefined": 1,
          "time": -IGNORE-VALUE-,
          "suites": [
              {
                  "name": "single_feature",
                  "tests": 9,
                  "skipped": 0,
                  "failed": 3,
                  "pending": 1,
                  "undefined": 1,
                  "time": -IGNORE-VALUE-,
                  "features": [
                      {
                          "name": "Adding numbers",
                          "file": "features-DIRECTORY-SEPARATOR-single_feature.feature",
                          "tests": 9,
                          "skipped": 0,
                          "failed": 3,
                          "pending": 1,
                          "undefined": 1,
                          "time": -IGNORE-VALUE-,
                          "scenarios": [
                              {
                                  "name": "Passed",
                                  "time": -IGNORE-VALUE-,
                                  "status": "passed",
                                  "file": "features-DIRECTORY-SEPARATOR-single_feature.feature",
                                  "line": 11
                              },
                              {
                                  "name": "Undefined",
                                  "time": -IGNORE-VALUE-,
                                  "status": "undefined",
                                  "file": "features-DIRECTORY-SEPARATOR-single_feature.feature",
                                  "line": 16,
                                  "failures": [
                                      {
                                          "message": "And Something new",
                                          "type": "undefined"
                                      }
                                  ]
                              },
                              {
                                  "name": "Pending",
                                  "time": -IGNORE-VALUE-,
                                  "status": "pending",
                                  "file": "features-DIRECTORY-SEPARATOR-single_feature.feature",
                                  "line": 21,
                                  "failures": [
                                      {
                                          "message": "And Something not done yet: TODO: write pending definition",
                                          "type": "pending"
                                      }
                                  ]
                              },
                              {
                                  "name": "Failed",
                                  "time": -IGNORE-VALUE-,
                                  "status": "failed",
                                  "file": "features-DIRECTORY-SEPARATOR-single_feature.feature",
                                  "line": 25,
                                  "failures": [
                                      {
                                          "message": "Then I must have 13: Failed asserting that 14 matches expected '13'.",
                                          "type": "failed"
                                      }
                                  ]
                              },
                              {
                                  "name": "Passed & Failed with value=5 #1",
                                  "time": -IGNORE-VALUE-,
                                  "status": "failed",
                                  "file": "features-DIRECTORY-SEPARATOR-single_feature.feature",
                                  "line": 29,
                                  "failures": [
                                      {
                                          "message": "Then I must have 16: Failed asserting that 15 matches expected '16'.",
                                          "type": "failed"
                                      }
                                  ]
                              },
                              {
                                  "name": "Passed & Failed with value=10 #2",
                                  "time": -IGNORE-VALUE-,
                                  "status": "passed",
                                  "file": "features-DIRECTORY-SEPARATOR-single_feature.feature",
                                  "line": 29
                              },
                              {
                                  "name": "Passed & Failed with value=23 #3",
                                  "time": -IGNORE-VALUE-,
                                  "status": "failed",
                                  "file": "features-DIRECTORY-SEPARATOR-single_feature.feature",
                                  "line": 29,
                                  "failures": [
                                      {
                                          "message": "Then I must have 32: Failed asserting that 33 matches expected '32'.",
                                          "type": "failed"
                                      }
                                  ]
                              },
                              {
                                  "name": "Another Outline (5 = 15) #1",
                                  "time": -IGNORE-VALUE-,
                                  "status": "passed",
                                  "file": "features-DIRECTORY-SEPARATOR-single_feature.feature",
                                  "line": 39
                              },
                              {
                                  "name": "Another Outline (10 = 20) #2",
                                  "time": -IGNORE-VALUE-,
                                  "status": "passed",
                                  "file": "features-DIRECTORY-SEPARATOR-single_feature.feature",
                                  "line": 39
                              }
                          ]
                      }
                  ]
              }
          ]
      }
      """
    And the file "single_feature.json" should be a valid document according to the json schema "schema.json"

  Scenario: Run multiple Features
    When I run behat with the following additional options:
      | option  | value                  |
      | --suite | multiple_features      |
      | --out   | multiple_features.json |
    Then it should pass with no output
    And the "multiple_features.json" file json should be like:
      """
      {
          "tests": 2,
          "skipped": 0,
          "failed": 0,
          "pending": 0,
          "undefined": 0,
          "time": -IGNORE-VALUE-,
          "suites": [
              {
                  "name": "multiple_features",
                  "tests": 2,
                  "skipped": 0,
                  "failed": 0,
                  "pending": 0,
                  "undefined": 0,
                  "time": -IGNORE-VALUE-,
                  "features": [
                      {
                          "name": "Adding Feature 1",
                          "file": "features-DIRECTORY-SEPARATOR-multiple_features_1.feature",
                          "tests": 1,
                          "skipped": 0,
                          "failed": 0,
                          "pending": 0,
                          "undefined": 0,
                          "time": -IGNORE-VALUE-,
                          "scenarios": [
                              {
                                  "name": "Adding 4 to 10",
                                  "time": -IGNORE-VALUE-,
                                  "status": "passed",
                                  "file": "features-DIRECTORY-SEPARATOR-multiple_features_1.feature",
                                  "line": 9
                              }
                          ]
                      },
                      {
                          "name": "Adding Feature 2",
                          "file": "features-DIRECTORY-SEPARATOR-multiple_features_2.feature",
                          "tests": 1,
                          "skipped": 0,
                          "failed": 0,
                          "pending": 0,
                          "undefined": 0,
                          "time": -IGNORE-VALUE-,
                          "scenarios": [
                              {
                                  "name": "Adding 8 to 10",
                                  "time": -IGNORE-VALUE-,
                                  "status": "passed",
                                  "file": "features-DIRECTORY-SEPARATOR-multiple_features_2.feature",
                                  "line": 9
                              }
                          ]
                      }
                  ]
              }
          ]
      }
      """
    And the file "multiple_features.json" should be a valid document according to the json schema "schema.json"

  Scenario: Confirm multiline scenario titles are printed correctly
    When I run behat with the following additional options:
      | option  | value                 |
      | --suite | multiline_titles .    |
      | --out   | multiline_titles.json |
    Then it should pass with no output
    And the "multiline_titles.json" file json should be like:
      """
      {
          "tests": 2,
          "skipped": 0,
          "failed": 0,
          "pending": 0,
          "undefined": 0,
          "time": -IGNORE-VALUE-,
          "suites": [
              {
                  "name": "multiline_titles",
                  "tests": 2,
                  "skipped": 0,
                  "failed": 0,
                  "pending": 0,
                  "undefined": 0,
                  "time": -IGNORE-VALUE-,
                  "features": [
                      {
                          "name": "Use multiline titles",
                          "file": "features-DIRECTORY-SEPARATOR-multiline_titles.feature",
                          "tests": 2,
                          "skipped": 0,
                          "failed": 0,
                          "pending": 0,
                          "undefined": 0,
                          "time": -IGNORE-VALUE-,
                          "scenarios": [
                              {
                                  "name": "Adding some interesting value",
                                  "time": -IGNORE-VALUE-,
                                  "status": "passed",
                                  "file": "features-DIRECTORY-SEPARATOR-multiline_titles.feature",
                                  "line": 13
                              },
                              {
                                  "name": "Adding another value",
                                  "time": -IGNORE-VALUE-,
                                  "status": "passed",
                                  "file": "features-DIRECTORY-SEPARATOR-multiline_titles.feature",
                                  "line": 20
                              }
                          ]
                      }
                  ]
              }
          ]
      }
      """
    And the file "multiline_titles.json" should be a valid document according to the json schema "schema.json"

  Scenario: Multiple suites
    When I run behat with the following additional options:
      | option   | value           |
      | --config | two_suites.php  |
      | --out    | two_suites.json |
    Then it should fail with no output
    And the "two_suites.json" file json should be like:
      """
      {
          "tests": 2,
          "skipped": 0,
          "failed": 1,
          "pending": 0,
          "undefined": 0,
          "time": -IGNORE-VALUE-,
          "suites": [
              {
                  "name": "small_kid",
                  "tests": 1,
                  "skipped": 0,
                  "failed": 0,
                  "pending": 0,
                  "undefined": 0,
                  "time": -IGNORE-VALUE-,
                  "features": [
                      {
                          "name": "Adding easy numbers",
                          "file": "features-DIRECTORY-SEPARATOR-multiple_suites_1.feature",
                          "tests": 1,
                          "skipped": 0,
                          "failed": 0,
                          "pending": 0,
                          "undefined": 0,
                          "time": -IGNORE-VALUE-,
                          "scenarios": [
                              {
                                  "name": "Easy sum",
                                  "time": -IGNORE-VALUE-,
                                  "status": "passed",
                                  "file": "features-DIRECTORY-SEPARATOR-multiple_suites_1.feature",
                                  "line": 11
                              }
                          ]
                      }
                  ]
              },
              {
                  "name": "old_man",
                  "tests": 1,
                  "skipped": 0,
                  "failed": 1,
                  "pending": 0,
                  "undefined": 0,
                  "time": -IGNORE-VALUE-,
                  "features": [
                      {
                          "name": "Adding difficult numbers",
                          "file": "features-DIRECTORY-SEPARATOR-multiple_suites_2.feature",
                          "tests": 1,
                          "skipped": 0,
                          "failed": 1,
                          "pending": 0,
                          "undefined": 0,
                          "time": -IGNORE-VALUE-,
                          "scenarios": [
                              {
                                  "name": "Difficult sum",
                                  "time": -IGNORE-VALUE-,
                                  "status": "failed",
                                  "file": "features-DIRECTORY-SEPARATOR-multiple_suites_2.feature",
                                  "line": 11,
                                  "failures": [
                                      {
                                          "message": "Then I must have 477: Failed asserting that 378 matches expected '477'.",
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
    And the file "two_suites.json" should be a valid document according to the json schema "schema.json"

  Scenario: Report skipped testcases
    When I run behat with the following additional options:
      | option  | value                   |
      | --suite | skipped_test_cases .    |
      | --out   | skipped_test_cases.json |
    Then it should fail with no output
    And the "skipped_test_cases.json" file json should be like:
      """
      {
          "tests": 2,
          "skipped": 2,
          "failed": 0,
          "pending": 0,
          "undefined": 0,
          "time": -IGNORE-VALUE-,
          "suites": [
              {
                  "name": "skipped_test_cases",
                  "tests": 2,
                  "skipped": 2,
                  "failed": 0,
                  "pending": 0,
                  "undefined": 0,
                  "time": -IGNORE-VALUE-,
                  "features": [
                      {
                          "name": "Skipped test cases",
                          "file": "features-DIRECTORY-SEPARATOR-skipped_test_cases.feature",
                          "tests": 2,
                          "skipped": 2,
                          "failed": 0,
                          "pending": 0,
                          "undefined": 0,
                          "time": -IGNORE-VALUE-,
                          "scenarios": [
                              {
                                  "name": "Skipped",
                                  "time": -IGNORE-VALUE-,
                                  "status": "skipped",
                                  "file": "features-DIRECTORY-SEPARATOR-skipped_test_cases.feature",
                                  "failures": [
                                      {
                                          "message": "BeforeScenario: This scenario has a failed setup (Exception)",
                                          "type": "setup"
                                      }
                                  ],
                                  "line": 11
                              },
                              {
                                  "name": "Another skipped",
                                  "time": -IGNORE-VALUE-,
                                  "status": "skipped",
                                  "file": "features-DIRECTORY-SEPARATOR-skipped_test_cases.feature",
                                  "failures": [
                                      {
                                          "message": "BeforeScenario: This scenario has a failed setup (Exception)",
                                          "type": "setup"
                                      }
                                  ],
                                  "line": 15
                              }
                          ]
                      }
                  ]
              }
          ]
      }
      """
    And the file "skipped_test_cases.json" should be a valid document according to the json schema "schema.json"

  Scenario: Stop on failure
    When I run behat with the following additional options:
      | option  | value                |
      | --suite | stop_on_failure      |
      | --out   | stop_on_failure.json |
    Then it should fail with no output
    And the "stop_on_failure.json" file json should be like:
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
                  "name": "stop_on_failure",
                  "tests": 1,
                  "skipped": 0,
                  "failed": 1,
                  "pending": 0,
                  "undefined": 0,
                  "time": -IGNORE-VALUE-,
                  "features": [
                      {
                          "name": "Stop on failure",
                          "file": "features-DIRECTORY-SEPARATOR-stop_on_failure.feature",
                          "tests": 1,
                          "skipped": 0,
                          "failed": 1,
                          "pending": 0,
                          "undefined": 0,
                          "time": -IGNORE-VALUE-,
                          "scenarios": [
                              {
                                  "name": "Failed",
                                  "time": -IGNORE-VALUE-,
                                  "status": "failed",
                                  "file": "features-DIRECTORY-SEPARATOR-stop_on_failure.feature",
                                  "line": 11,
                                  "failures": [
                                      {
                                          "message": "Then I must have 13: Failed asserting that 14 matches expected '13'.",
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
    And the file "stop_on_failure.json" should be a valid document according to the json schema "schema.json"

  Scenario: Aborting due to PHP error
    When I run behat with the following additional options:
      | option  | value                   |
      | --suite | abort_on_php_error      |
      | --out   | abort_on_php_error.json |
    Then it should fail with:
      """
      cannot extend interface Behat\Behat\Context\Context
      """
    And the "abort_on_php_error.json" file should not exist

  Scenario: Aborting due to invalid output path
    When I run behat with the following additional options:
      | option  | value          |
      | --suite | single_feature |
      | --out   | ./features     |
    Then it should fail with:
      """
      A file name expected for the `output_path` option, but a directory was given.
      """
