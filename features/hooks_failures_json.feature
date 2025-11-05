Feature: Display hook failures location in json printer
  In order to be able to locate the code that generated a failure
  As a feature developer using the json printer
  When a hook throws an error I want to see the related item where the code failed

  Background:
    Given I initialise the working directory from the "HookFailures" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value |
      | --no-colors |       |
      | --format    | json  |

  Scenario: Handling of a error in beforeSuite hook
    When I run behat with the following additional options:
      | option    | value            |
      | --profile | beforeSuite      |
      | --out     | beforesuite.json |
    Then it should fail
    And the "beforesuite.json" file json should be like:
      """
      {
          "tests": 3,
          "skipped": 3,
          "failed": 0,
          "pending": 0,
          "undefined": 0,
          "suites": [
              {
                  "name": "default",
                  "tests": 3,
                  "skipped": 3,
                  "failed": 0,
                  "pending": 0,
                  "undefined": 0,
                  "features": [
                      {
                          "name": "First feature",
                          "file": "features-DIRECTORY-SEPARATOR-one.feature",
                          "tests": 2,
                          "skipped": 2,
                          "failed": 0,
                          "pending": 0,
                          "undefined": 0,
                          "scenarios": [
                              {
                                  "name": "First scenario",
                                  "status": "skipped",
                                  "file": "features-DIRECTORY-SEPARATOR-one.feature",
                                  "line": 5
                              },
                              {
                                  "name": "Second scenario",
                                  "status": "skipped",
                                  "file": "features-DIRECTORY-SEPARATOR-one.feature",
                                  "line": 8
                              }
                          ]
                      },
                      {
                          "name": "Second feature",
                          "file": "features-DIRECTORY-SEPARATOR-two.feature",
                          "tests": 1,
                          "skipped": 1,
                          "failed": 0,
                          "pending": 0,
                          "undefined": 0,
                          "scenarios": [
                              {
                                  "name": "First scenario",
                                  "status": "skipped",
                                  "file": "features-DIRECTORY-SEPARATOR-two.feature",
                                  "line": 4
                              }
                          ]
                      }
                  ],
                  "failures": [
                      {
                          "message": "BeforeSuite: Error in beforeSuite hook (Exception)",
                          "type": "setup"
                      }
                  ]
              }
          ]
      }
      """
    And the file "beforesuite.json" should be a valid document according to the json schema "schema.json"

  Scenario: Handling of a error in afterSuite hook
    When I run behat with the following additional options:
      | option    | value           |
      | --profile | afterSuite      |
      | --out     | aftersuite.json |
    Then it should fail
    And the "aftersuite.json" file json should be like:
      """
      {
          "tests": 3,
          "skipped": 0,
          "failed": 0,
          "pending": 0,
          "undefined": 0,
          "suites": [
              {
                  "name": "default",
                  "tests": 3,
                  "skipped": 0,
                  "failed": 0,
                  "pending": 0,
                  "undefined": 0,
                  "features": [
                      {
                          "name": "First feature",
                          "file": "features-DIRECTORY-SEPARATOR-one.feature",
                          "tests": 2,
                          "skipped": 0,
                          "failed": 0,
                          "pending": 0,
                          "undefined": 0,
                          "scenarios": [
                              {
                                  "name": "First scenario",
                                  "status": "passed",
                                  "file": "features-DIRECTORY-SEPARATOR-one.feature",
                                  "line": 5
                              },
                              {
                                  "name": "Second scenario",
                                  "status": "passed",
                                  "file": "features-DIRECTORY-SEPARATOR-one.feature",
                                  "line": 8
                              }
                          ]
                      },
                      {
                          "name": "Second feature",
                          "file": "features-DIRECTORY-SEPARATOR-two.feature",
                          "tests": 1,
                          "skipped": 0,
                          "failed": 0,
                          "pending": 0,
                          "undefined": 0,
                          "scenarios": [
                              {
                                  "name": "First scenario",
                                  "status": "passed",
                                  "file": "features-DIRECTORY-SEPARATOR-two.feature",
                                  "line": 4
                              }
                          ]
                      }
                  ],
                  "failures": [
                      {
                          "message": "AfterSuite: Error in afterSuite hook (Exception)",
                          "type": "teardown"
                      }
                  ]
              }
          ]
      }
      """
    And the file "aftersuite.json" should be a valid document according to the json schema "schema.json"

  Scenario: Handling of a error in beforeFeature hook
    When I run behat with the following additional options:
      | option    | value             |
      | --profile | beforeFeature     |
      | --out     | beforefeature.json |
    Then it should fail
    And the "beforefeature.json" file json should be like:
      """
      {
          "tests": 3,
          "skipped": 2,
          "failed": 0,
          "pending": 0,
          "undefined": 0,
          "suites": [
              {
                  "name": "default",
                  "tests": 3,
                  "skipped": 2,
                  "failed": 0,
                  "pending": 0,
                  "undefined": 0,
                  "features": [
                      {
                          "name": "First feature",
                          "file": "features-DIRECTORY-SEPARATOR-one.feature",
                          "tests": 2,
                          "skipped": 2,
                          "failed": 0,
                          "pending": 0,
                          "undefined": 0,
                          "scenarios": [
                              {
                                  "name": "First scenario",
                                  "status": "skipped",
                                  "file": "features-DIRECTORY-SEPARATOR-one.feature",
                                  "line": 5
                              },
                              {
                                  "name": "Second scenario",
                                  "status": "skipped",
                                  "file": "features-DIRECTORY-SEPARATOR-one.feature",
                                  "line": 8
                              }
                          ],
                          "failures": [
                              {
                                  "message": "BeforeFeature: Error in beforeFeature hook (Exception)",
                                  "type": "setup"
                              }
                          ]
                      },
                      {
                          "name": "Second feature",
                          "file": "features-DIRECTORY-SEPARATOR-two.feature",
                          "tests": 1,
                          "skipped": 0,
                          "failed": 0,
                          "pending": 0,
                          "undefined": 0,
                          "scenarios": [
                              {
                                  "name": "First scenario",
                                  "status": "passed",
                                  "file": "features-DIRECTORY-SEPARATOR-two.feature",
                                  "line": 4
                              }
                          ]
                      }
                  ]
              }
          ]
      }
      """
    And the file "beforefeature.json" should be a valid document according to the json schema "schema.json"

  Scenario: Handling of a error in afterFeature hook
    When I run behat with the following additional options:
      | option    | value            |
      | --profile | afterFeature     |
      | --out     | afterfeature.json |
    Then it should fail
    And the "afterfeature.json" file json should be like:
      """
      {
          "tests": 3,
          "skipped": 0,
          "failed": 0,
          "pending": 0,
          "undefined": 0,
          "suites": [
              {
                  "name": "default",
                  "tests": 3,
                  "skipped": 0,
                  "failed": 0,
                  "pending": 0,
                  "undefined": 0,
                  "features": [
                      {
                          "name": "First feature",
                          "file": "features-DIRECTORY-SEPARATOR-one.feature",
                          "tests": 2,
                          "skipped": 0,
                          "failed": 0,
                          "pending": 0,
                          "undefined": 0,
                          "scenarios": [
                              {
                                  "name": "First scenario",
                                  "status": "passed",
                                  "file": "features-DIRECTORY-SEPARATOR-one.feature",
                                  "line": 5
                              },
                              {
                                  "name": "Second scenario",
                                  "status": "passed",
                                  "file": "features-DIRECTORY-SEPARATOR-one.feature",
                                  "line": 8
                              }
                          ],
                          "failures": [
                              {
                                  "message": "AfterFeature: Error in afterFeature hook (Exception)",
                                  "type": "teardown"
                              }
                          ]
                      },
                      {
                          "name": "Second feature",
                          "file": "features-DIRECTORY-SEPARATOR-two.feature",
                          "tests": 1,
                          "skipped": 0,
                          "failed": 0,
                          "pending": 0,
                          "undefined": 0,
                          "scenarios": [
                              {
                                  "name": "First scenario",
                                  "status": "passed",
                                  "file": "features-DIRECTORY-SEPARATOR-two.feature",
                                  "line": 4
                              }
                          ]
                      }
                  ]
              }
          ]
      }
      """
    And the file "afterfeature.json" should be a valid document according to the json schema "schema.json"

  Scenario: Handling of a error in beforeScenario hook
    When I run behat with the following additional options:
      | option    | value              |
      | --profile | beforeScenario     |
      | --out     | beforescenario.json |
    Then it should fail
    And the "beforescenario.json" file json should be like:
      """
      {
          "tests": 3,
          "skipped": 1,
          "failed": 0,
          "pending": 0,
          "undefined": 0,
          "suites": [
              {
                  "name": "default",
                  "tests": 3,
                  "skipped": 1,
                  "failed": 0,
                  "pending": 0,
                  "undefined": 0,
                  "features": [
                      {
                          "name": "First feature",
                          "file": "features-DIRECTORY-SEPARATOR-one.feature",
                          "tests": 2,
                          "skipped": 1,
                          "failed": 0,
                          "pending": 0,
                          "undefined": 0,
                          "scenarios": [
                              {
                                  "name": "First scenario",
                                  "status": "skipped",
                                  "file": "features-DIRECTORY-SEPARATOR-one.feature",
                                  "failures": [
                                      {
                                          "message": "BeforeScenario: Error in beforeScenario hook (Exception)",
                                          "type": "setup"
                                      }
                                  ],
                                  "line": 5
                              },
                              {
                                  "name": "Second scenario",
                                  "status": "passed",
                                  "file": "features-DIRECTORY-SEPARATOR-one.feature",
                                  "line": 8
                              }
                          ]
                      },
                      {
                          "name": "Second feature",
                          "file": "features-DIRECTORY-SEPARATOR-two.feature",
                          "tests": 1,
                          "skipped": 0,
                          "failed": 0,
                          "pending": 0,
                          "undefined": 0,
                          "scenarios": [
                              {
                                  "name": "First scenario",
                                  "status": "passed",
                                  "file": "features-DIRECTORY-SEPARATOR-two.feature",
                                  "line": 4
                              }
                          ]
                      }
                  ]
              }
          ]
      }
      """
    And the file "beforescenario.json" should be a valid document according to the json schema "schema.json"

  Scenario: Handling of a error in afterScenario hook
    When I run behat with the following additional options:
      | option    | value             |
      | --profile | afterScenario     |
      | --out     | afterscenario.json |
    Then it should fail
    And the "afterscenario.json" file json should be like:
      """
      {
          "tests": 3,
          "skipped": 0,
          "failed": 0,
          "pending": 0,
          "undefined": 0,
          "suites": [
              {
                  "name": "default",
                  "tests": 3,
                  "skipped": 0,
                  "failed": 0,
                  "pending": 0,
                  "undefined": 0,
                  "features": [
                      {
                          "name": "First feature",
                          "file": "features-DIRECTORY-SEPARATOR-one.feature",
                          "tests": 2,
                          "skipped": 0,
                          "failed": 0,
                          "pending": 0,
                          "undefined": 0,
                          "scenarios": [
                              {
                                  "name": "First scenario",
                                  "status": "passed",
                                  "file": "features-DIRECTORY-SEPARATOR-one.feature",
                                  "line": 5,
                                  "failures": [
                                      {
                                          "message": "AfterScenario: Error in afterScenario hook (Exception)",
                                          "type": "teardown"
                                      }
                                  ]
                              },
                              {
                                  "name": "Second scenario",
                                  "status": "passed",
                                  "file": "features-DIRECTORY-SEPARATOR-one.feature",
                                  "line": 8
                              }
                          ]
                      },
                      {
                          "name": "Second feature",
                          "file": "features-DIRECTORY-SEPARATOR-two.feature",
                          "tests": 1,
                          "skipped": 0,
                          "failed": 0,
                          "pending": 0,
                          "undefined": 0,
                          "scenarios": [
                              {
                                  "name": "First scenario",
                                  "status": "passed",
                                  "file": "features-DIRECTORY-SEPARATOR-two.feature",
                                  "line": 4
                              }
                          ]
                      }
                  ]
              }
          ]
      }
      """
    And the file "afterscenario.json" should be a valid document according to the json schema "schema.json"

  Scenario: Handling of a error in beforeStep hook
    When I run behat with the following additional options:
      | option    | value            |
      | --profile | beforeStep       |
      | --out     | beforestep.json |
    Then it should fail
    And the "beforestep.json" file json should be like:
      """
      {
          "tests": 3,
          "skipped": 0,
          "failed": 1,
          "pending": 0,
          "undefined": 0,
          "suites": [
              {
                  "name": "default",
                  "tests": 3,
                  "skipped": 0,
                  "failed": 1,
                  "pending": 0,
                  "undefined": 0,
                  "features": [
                      {
                          "name": "First feature",
                          "file": "features-DIRECTORY-SEPARATOR-one.feature",
                          "tests": 2,
                          "skipped": 0,
                          "failed": 1,
                          "pending": 0,
                          "undefined": 0,
                          "scenarios": [
                              {
                                  "name": "First scenario",
                                  "status": "failed",
                                  "file": "features-DIRECTORY-SEPARATOR-one.feature",
                                  "failures": [
                                      {
                                          "message": "BeforeStep: When I have a simple step: Error in beforeStep hook (Exception)",
                                          "type": "setup"
                                      }
                                  ],
                                  "line": 5
                              },
                              {
                                  "name": "Second scenario",
                                  "status": "passed",
                                  "file": "features-DIRECTORY-SEPARATOR-one.feature",
                                  "line": 8
                              }
                          ]
                      },
                      {
                          "name": "Second feature",
                          "file": "features-DIRECTORY-SEPARATOR-two.feature",
                          "tests": 1,
                          "skipped": 0,
                          "failed": 0,
                          "pending": 0,
                          "undefined": 0,
                          "scenarios": [
                              {
                                  "name": "First scenario",
                                  "status": "passed",
                                  "file": "features-DIRECTORY-SEPARATOR-two.feature",
                                  "line": 4
                              }
                          ]
                      }
                  ]
              }
          ]
      }
      """
    And the file "beforestep.json" should be a valid document according to the json schema "schema.json"

  Scenario: Handling of a error in afterStep hook
    When I run behat with the following additional options:
      | option    | value           |
      | --profile | afterStep       |
      | --out     | afterstep.json |
    Then it should fail
    And the "afterstep.json" file json should be like:
      """
      {
          "tests": 3,
          "skipped": 0,
          "failed": 1,
          "pending": 0,
          "undefined": 0,
          "suites": [
              {
                  "name": "default",
                  "tests": 3,
                  "skipped": 0,
                  "failed": 1,
                  "pending": 0,
                  "undefined": 0,
                  "features": [
                      {
                          "name": "First feature",
                          "file": "features-DIRECTORY-SEPARATOR-one.feature",
                          "tests": 2,
                          "skipped": 0,
                          "failed": 1,
                          "pending": 0,
                          "undefined": 0,
                          "scenarios": [
                              {
                                  "name": "First scenario",
                                  "status": "failed",
                                  "file": "features-DIRECTORY-SEPARATOR-one.feature",
                                  "line": 5,
                                  "failures": [
                                      {
                                          "message": "AfterStep: When I have a simple step: Error in afterStep hook (Exception)",
                                          "type": "teardown"
                                      }
                                  ]
                              },
                              {
                                  "name": "Second scenario",
                                  "status": "passed",
                                  "file": "features-DIRECTORY-SEPARATOR-one.feature",
                                  "line": 8
                              }
                          ]
                      },
                      {
                          "name": "Second feature",
                          "file": "features-DIRECTORY-SEPARATOR-two.feature",
                          "tests": 1,
                          "skipped": 0,
                          "failed": 0,
                          "pending": 0,
                          "undefined": 0,
                          "scenarios": [
                              {
                                  "name": "First scenario",
                                  "status": "passed",
                                  "file": "features-DIRECTORY-SEPARATOR-two.feature",
                                  "line": 4
                              }
                          ]
                      }
                  ]
              }
          ]
      }
      """
    And the file "afterstep.json" should be a valid document according to the json schema "schema.json"
