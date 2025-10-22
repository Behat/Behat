Feature: Display hook failures location in json printer
  In order to be able to locate the code that generated a failure
  As a feature developer using the json printer
  When a hook throws an error I want to see the related item where the code failed

  Background:
    Given a file named "features/one.feature" with:
      """
      Feature: First feature

        Scenario: First scenario
          When I have a simple step
          And I have a simple step

        Scenario: Second scenario
          When I have a simple step
      """
    Given a file named "features/two.feature" with:
      """
      Feature: Second feature

        Scenario: First scenario
          When I have a simple step
      """

  Scenario: Handling of a error in beforeSuite hook
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;
      use Behat\Hook\BeforeSuite;
      use Behat\Step\When;

      class FeatureContext implements Context
      {
          #[BeforeSuite]
          public static function beforeSuiteHook()
          {
              throw new \Exception('Error in beforeSuite hook');
          }

          #[When('I have a simple step')]
          public function iHaveASimpleStep()
          {
          }
      }
      """
    When I run "behat --no-colors --format=json --out=beforesuite.json"
    Then it should fail
    And the "beforesuite.json" file json should be like:
      """
      {
          "tests": 3,
          "skipped": 3,
          "failed": 0,
          "pending": 0,
          "undefined": 0,
          "time": -IGNORE-VALUE-,
          "suites": [
              {
                  "name": "default",
                  "tests": 3,
                  "skipped": 3,
                  "failed": 0,
                  "pending": 0,
                  "undefined": 0,
                  "time": -IGNORE-VALUE-,
                  "features": [
                      {
                          "name": "First feature",
                          "tests": 2,
                          "skipped": 2,
                          "failed": 0,
                          "pending": 0,
                          "undefined": 0,
                          "time": -IGNORE-VALUE-,
                          "scenarios": [
                              {
                                  "name": "First scenario",
                                  "time": -IGNORE-VALUE-,
                                  "status": "skipped",
                                  "file": "features-DIRECTORY-SEPARATOR-one.feature",
                                  "line": 5
                              },
                              {
                                  "name": "Second scenario",
                                  "time": -IGNORE-VALUE-,
                                  "status": "skipped",
                                  "file": "features-DIRECTORY-SEPARATOR-one.feature",
                                  "line": 8
                              }
                          ]
                      },
                      {
                          "name": "Second feature",
                          "tests": 1,
                          "skipped": 1,
                          "failed": 0,
                          "pending": 0,
                          "undefined": 0,
                          "time": -IGNORE-VALUE-,
                          "scenarios": [
                              {
                                  "name": "First scenario",
                                  "time": -IGNORE-VALUE-,
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
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;
      use Behat\Hook\AfterSuite;
      use Behat\Step\When;

      class FeatureContext implements Context
      {
          #[AfterSuite]
          public static function afterSuiteHook()
          {
              throw new \Exception('Error in afterSuite hook');
          }

          #[When('I have a simple step')]
          public function iHaveASimpleStep()
          {
          }
      }
      """
    When I run "behat --no-colors --format=json --out=aftersuite.json"
    Then it should fail
    And the "aftersuite.json" file json should be like:
      """
      {
          "tests": 3,
          "skipped": 0,
          "failed": 0,
          "pending": 0,
          "undefined": 0,
          "time": -IGNORE-VALUE-,
          "suites": [
              {
                  "name": "default",
                  "tests": 3,
                  "skipped": 0,
                  "failed": 0,
                  "pending": 0,
                  "undefined": 0,
                  "time": -IGNORE-VALUE-,
                  "features": [
                      {
                          "name": "First feature",
                          "tests": 2,
                          "skipped": 0,
                          "failed": 0,
                          "pending": 0,
                          "undefined": 0,
                          "time": -IGNORE-VALUE-,
                          "scenarios": [
                              {
                                  "name": "First scenario",
                                  "time": -IGNORE-VALUE-,
                                  "status": "passed",
                                  "file": "features-DIRECTORY-SEPARATOR-one.feature",
                                  "line": 5
                              },
                              {
                                  "name": "Second scenario",
                                  "time": -IGNORE-VALUE-,
                                  "status": "passed",
                                  "file": "features-DIRECTORY-SEPARATOR-one.feature",
                                  "line": 8
                              }
                          ]
                      },
                      {
                          "name": "Second feature",
                          "tests": 1,
                          "skipped": 0,
                          "failed": 0,
                          "pending": 0,
                          "undefined": 0,
                          "time": -IGNORE-VALUE-,
                          "scenarios": [
                              {
                                  "name": "First scenario",
                                  "time": -IGNORE-VALUE-,
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
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;
      use Behat\Hook\BeforeFeature;
      use Behat\Step\When;

      class FeatureContext implements Context
      {
          private static $hasThrownError = false;

          #[BeforeFeature]
          public static function beforeFeatureHook()
          {
              if (!self::$hasThrownError) {
                  self::$hasThrownError = true;
                  throw new \Exception('Error in beforeFeature hook');
              }
          }

          #[When('I have a simple step')]
          public function iHaveASimpleStep()
          {
          }
      }
      """
    When I run "behat --no-colors --format=json --out=beforefeature.json"
    Then it should fail
    And the "beforefeature.json" file json should be like:
      """
      {
          "tests": 3,
          "skipped": 2,
          "failed": 0,
          "pending": 0,
          "undefined": 0,
          "time": -IGNORE-VALUE-,
          "suites": [
              {
                  "name": "default",
                  "tests": 3,
                  "skipped": 2,
                  "failed": 0,
                  "pending": 0,
                  "undefined": 0,
                  "time": -IGNORE-VALUE-,
                  "features": [
                      {
                          "name": "First feature",
                          "tests": 2,
                          "skipped": 2,
                          "failed": 0,
                          "pending": 0,
                          "undefined": 0,
                          "time": -IGNORE-VALUE-,
                          "scenarios": [
                              {
                                  "name": "First scenario",
                                  "time": -IGNORE-VALUE-,
                                  "status": "skipped",
                                  "file": "features-DIRECTORY-SEPARATOR-one.feature",
                                  "line": 5
                              },
                              {
                                  "name": "Second scenario",
                                  "time": -IGNORE-VALUE-,
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
                          "tests": 1,
                          "skipped": 0,
                          "failed": 0,
                          "pending": 0,
                          "undefined": 0,
                          "time": -IGNORE-VALUE-,
                          "scenarios": [
                              {
                                  "name": "First scenario",
                                  "time": -IGNORE-VALUE-,
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
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;
      use Behat\Hook\AfterFeature;
      use Behat\Step\When;

      class FeatureContext implements Context
      {
          private static $hasThrownError = false;

          #[AfterFeature]
          public static function afterFeatureHook()
          {
              if (!self::$hasThrownError) {
                  self::$hasThrownError = true;
                  throw new \Exception('Error in afterFeature hook');
              }
          }

          #[When('I have a simple step')]
          public function iHaveASimpleStep()
          {
          }
      }
      """
    When I run "behat --no-colors --format=json --out=afterfeature.json"
    Then it should fail
    And the "afterfeature.json" file json should be like:
      """
      {
          "tests": 3,
          "skipped": 0,
          "failed": 0,
          "pending": 0,
          "undefined": 0,
          "time": -IGNORE-VALUE-,
          "suites": [
              {
                  "name": "default",
                  "tests": 3,
                  "skipped": 0,
                  "failed": 0,
                  "pending": 0,
                  "undefined": 0,
                  "time": -IGNORE-VALUE-,
                  "features": [
                      {
                          "name": "First feature",
                          "tests": 2,
                          "skipped": 0,
                          "failed": 0,
                          "pending": 0,
                          "undefined": 0,
                          "time": -IGNORE-VALUE-,
                          "scenarios": [
                              {
                                  "name": "First scenario",
                                  "time": -IGNORE-VALUE-,
                                  "status": "passed",
                                  "file": "features-DIRECTORY-SEPARATOR-one.feature",
                                  "line": 5
                              },
                              {
                                  "name": "Second scenario",
                                  "time": -IGNORE-VALUE-,
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
                          "tests": 1,
                          "skipped": 0,
                          "failed": 0,
                          "pending": 0,
                          "undefined": 0,
                          "time": -IGNORE-VALUE-,
                          "scenarios": [
                              {
                                  "name": "First scenario",
                                  "time": -IGNORE-VALUE-,
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
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;
      use Behat\Hook\BeforeScenario;
      use Behat\Step\When;

      class FeatureContext implements Context
      {
          private static $hasThrownError = false;

          #[BeforeScenario]
          public function beforeScenarioHook()
          {
              if (!self::$hasThrownError) {
                  self::$hasThrownError = true;
                  throw new \Exception('Error in beforeScenario hook');
              }
          }

          #[When('I have a simple step')]
          public function iHaveASimpleStep()
          {
          }
      }
      """
    When I run "behat --no-colors --format=json --out=beforescenario.json"
    Then it should fail
    And the "beforescenario.json" file json should be like:
      """
      {
          "tests": 3,
          "skipped": 1,
          "failed": 0,
          "pending": 0,
          "undefined": 0,
          "time": -IGNORE-VALUE-,
          "suites": [
              {
                  "name": "default",
                  "tests": 3,
                  "skipped": 1,
                  "failed": 0,
                  "pending": 0,
                  "undefined": 0,
                  "time": -IGNORE-VALUE-,
                  "features": [
                      {
                          "name": "First feature",
                          "tests": 2,
                          "skipped": 1,
                          "failed": 0,
                          "pending": 0,
                          "undefined": 0,
                          "time": -IGNORE-VALUE-,
                          "scenarios": [
                              {
                                  "name": "First scenario",
                                  "time": -IGNORE-VALUE-,
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
                                  "time": -IGNORE-VALUE-,
                                  "status": "passed",
                                  "file": "features-DIRECTORY-SEPARATOR-one.feature",
                                  "line": 8
                              }
                          ]
                      },
                      {
                          "name": "Second feature",
                          "tests": 1,
                          "skipped": 0,
                          "failed": 0,
                          "pending": 0,
                          "undefined": 0,
                          "time": -IGNORE-VALUE-,
                          "scenarios": [
                              {
                                  "name": "First scenario",
                                  "time": -IGNORE-VALUE-,
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
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;
      use Behat\Hook\AfterScenario;
      use Behat\Step\When;

      class FeatureContext implements Context
      {
          private static $hasThrownError = false;

          #[AfterScenario]
          public function afterScenarioHook()
          {
              if (!self::$hasThrownError) {
                  self::$hasThrownError = true;
                  throw new \Exception('Error in afterScenario hook');
              }
          }

          #[When('I have a simple step')]
          public function iHaveASimpleStep()
          {
          }
      }
      """
    When I run "behat --no-colors --format=json --out=afterscenario.json"
    Then it should fail
    And the "afterscenario.json" file json should be like:
      """
      {
          "tests": 3,
          "skipped": 0,
          "failed": 0,
          "pending": 0,
          "undefined": 0,
          "time": -IGNORE-VALUE-,
          "suites": [
              {
                  "name": "default",
                  "tests": 3,
                  "skipped": 0,
                  "failed": 0,
                  "pending": 0,
                  "undefined": 0,
                  "time": -IGNORE-VALUE-,
                  "features": [
                      {
                          "name": "First feature",
                          "tests": 2,
                          "skipped": 0,
                          "failed": 0,
                          "pending": 0,
                          "undefined": 0,
                          "time": -IGNORE-VALUE-,
                          "scenarios": [
                              {
                                  "name": "First scenario",
                                  "time": -IGNORE-VALUE-,
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
                                  "time": -IGNORE-VALUE-,
                                  "status": "passed",
                                  "file": "features-DIRECTORY-SEPARATOR-one.feature",
                                  "line": 8
                              }
                          ]
                      },
                      {
                          "name": "Second feature",
                          "tests": 1,
                          "skipped": 0,
                          "failed": 0,
                          "pending": 0,
                          "undefined": 0,
                          "time": -IGNORE-VALUE-,
                          "scenarios": [
                              {
                                  "name": "First scenario",
                                  "time": -IGNORE-VALUE-,
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
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;
      use Behat\Hook\BeforeStep;
      use Behat\Step\When;

      class FeatureContext implements Context
      {
          private static $hasThrownError = false;

          #[BeforeStep]
          public function beforeStepHook()
          {
              if (!self::$hasThrownError) {
                  self::$hasThrownError = true;
                  throw new \Exception('Error in beforeStep hook');
              }
          }

          #[When('I have a simple step')]
          public function iHaveASimpleStep()
          {
          }
      }
      """
    When I run "behat --no-colors --format=json --out=beforestep.json"
    Then it should fail
    And the "beforestep.json" file json should be like:
      """
      {
          "tests": 3,
          "skipped": 0,
          "failed": 1,
          "pending": 0,
          "undefined": 0,
          "time": -IGNORE-VALUE-,
          "suites": [
              {
                  "name": "default",
                  "tests": 3,
                  "skipped": 0,
                  "failed": 1,
                  "pending": 0,
                  "undefined": 0,
                  "time": -IGNORE-VALUE-,
                  "features": [
                      {
                          "name": "First feature",
                          "tests": 2,
                          "skipped": 0,
                          "failed": 1,
                          "pending": 0,
                          "undefined": 0,
                          "time": -IGNORE-VALUE-,
                          "scenarios": [
                              {
                                  "name": "First scenario",
                                  "time": -IGNORE-VALUE-,
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
                                  "time": -IGNORE-VALUE-,
                                  "status": "passed",
                                  "file": "features-DIRECTORY-SEPARATOR-one.feature",
                                  "line": 8
                              }
                          ]
                      },
                      {
                          "name": "Second feature",
                          "tests": 1,
                          "skipped": 0,
                          "failed": 0,
                          "pending": 0,
                          "undefined": 0,
                          "time": -IGNORE-VALUE-,
                          "scenarios": [
                              {
                                  "name": "First scenario",
                                  "time": -IGNORE-VALUE-,
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
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;
      use Behat\Hook\AfterStep;
      use Behat\Step\When;

      class FeatureContext implements Context
      {
          private static $hasThrownError = false;

          #[AfterStep]
          public function afterStepHook()
          {
              if (!self::$hasThrownError) {
                  self::$hasThrownError = true;
                  throw new \Exception('Error in afterStep hook');
              }
          }

          #[When('I have a simple step')]
          public function iHaveASimpleStep()
          {
          }
      }
      """
    When I run "behat --no-colors --format=json --out=afterstep.json"
    Then it should fail
    And the "afterstep.json" file json should be like:
      """
      {
          "tests": 3,
          "skipped": 0,
          "failed": 1,
          "pending": 0,
          "undefined": 0,
          "time": -IGNORE-VALUE-,
          "suites": [
              {
                  "name": "default",
                  "tests": 3,
                  "skipped": 0,
                  "failed": 1,
                  "pending": 0,
                  "undefined": 0,
                  "time": -IGNORE-VALUE-,
                  "features": [
                      {
                          "name": "First feature",
                          "tests": 2,
                          "skipped": 0,
                          "failed": 1,
                          "pending": 0,
                          "undefined": 0,
                          "time": -IGNORE-VALUE-,
                          "scenarios": [
                              {
                                  "name": "First scenario",
                                  "time": -IGNORE-VALUE-,
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
                                  "time": -IGNORE-VALUE-,
                                  "status": "passed",
                                  "file": "features-DIRECTORY-SEPARATOR-one.feature",
                                  "line": 8
                              }
                          ]
                      },
                      {
                          "name": "Second feature",
                          "tests": 1,
                          "skipped": 0,
                          "failed": 0,
                          "pending": 0,
                          "undefined": 0,
                          "time": -IGNORE-VALUE-,
                          "scenarios": [
                              {
                                  "name": "First scenario",
                                  "time": -IGNORE-VALUE-,
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
