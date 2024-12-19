Feature: Display hook failures location in junit printer using annotations
  In order to be able to locate the code that generated a failure
  As a feature developer using the junit printer
  When a hook throws an error I want to see the related item where the code failed using annotations

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

      class FeatureContext implements Context
      {
          /**
           * @BeforeSuite
           */
          public static function beforeSuiteHook()
          {
              throw new \Exception('Error in beforeSuite hook');
          }

          /**
           * @When I have a simple step
           */
          public function iHaveASimpleStep()
          {
          }
      }
      """
    When I run "behat --no-colors --format=junit --out=junit"
    Then it should fail
    And "junit/default.xml" file xml should be like:
      """
      <?xml version="1.0" encoding="UTF-8"?>
      <testsuites name="default">
        <testsuite name="First feature" tests="2" skipped="2" failures="0" errors="0" time="-IGNORE-VALUE-">
          <testcase name="First scenario" classname="First feature" status="skipped" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-one.feature">
            <failure message="BeforeSuite: Error in beforeSuite hook (Exception)" type="setup"></failure>
          </testcase>
          <testcase name="Second scenario" classname="First feature" status="skipped" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-one.feature"></testcase>
        </testsuite>
        <testsuite name="Second feature" tests="1" skipped="1" failures="0" errors="0" time="-IGNORE-VALUE-">
          <testcase name="First scenario" classname="Second feature" status="skipped" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-two.feature"></testcase>
        </testsuite>
      </testsuites>
      """
    And the file "junit/default.xml" should be a valid document according to "junit.xsd"

  Scenario: Handling of a error in afterSuite hook
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          /**
           * @AfterSuite
           */
          public static function afterSuiteHook()
          {
              throw new \Exception('Error in afterSuite hook');
          }

          /**
           * @When I have a simple step
           */
          public function iHaveASimpleStep()
          {
          }
      }
      """
    When I run "behat --no-colors --format=junit --out=junit"
    Then it should fail
    And "junit/default.xml" file xml should be like:
      """
      <?xml version="1.0" encoding="UTF-8"?>
      <testsuites name="default">
        <testsuite name="First feature" tests="2" skipped="0" failures="0" errors="0" time="-IGNORE-VALUE-">
          <testcase name="First scenario" classname="First feature" status="passed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-one.feature"></testcase>
          <testcase name="Second scenario" classname="First feature" status="passed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-one.feature"></testcase>
        </testsuite>
        <testsuite name="Second feature" tests="1" skipped="0" failures="0" errors="0" time="-IGNORE-VALUE-">
          <testcase name="First scenario" classname="Second feature" status="passed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-two.feature">
            <failure message="AfterSuite: Error in afterSuite hook (Exception)" type="teardown"></failure>
          </testcase>
        </testsuite>
      </testsuites>
      """
    And the file "junit/default.xml" should be a valid document according to "junit.xsd"

  Scenario: Handling of a error in beforeFeature hook
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          private static $hasThrownError = false;

          /**
           * @BeforeFeature
           */
          public static function beforeFeatureHook()
          {
              if (!self::$hasThrownError) {
                  self::$hasThrownError = true;
                  throw new \Exception('Error in beforeFeature hook');
              }
          }

          /**
           * @When I have a simple step
           */
          public function iHaveASimpleStep()
          {
          }
      }
      """
    When I run "behat --no-colors --format=junit --out=junit"
    Then it should fail
    And "junit/default.xml" file xml should be like:
      """
      <?xml version="1.0" encoding="UTF-8"?>
      <testsuites name="default">
        <testsuite name="First feature" tests="2" skipped="2" failures="0" errors="0" time="-IGNORE-VALUE-">
          <testcase name="First scenario" classname="First feature" status="skipped" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-one.feature">
            <failure message="BeforeFeature: Error in beforeFeature hook (Exception)" type="setup"></failure>
          </testcase>
          <testcase name="Second scenario" classname="First feature" status="skipped" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-one.feature"></testcase>
        </testsuite>
        <testsuite name="Second feature" tests="1" skipped="0" failures="0" errors="0" time="-IGNORE-VALUE-">
          <testcase name="First scenario" classname="Second feature" status="passed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-two.feature"></testcase>
        </testsuite>
      </testsuites>
      """
    And the file "junit/default.xml" should be a valid document according to "junit.xsd"

  Scenario: Handling of a error in afterFeature hook
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          private static $hasThrownError = false;

          /**
           * @AfterFeature
           */
          public static function afterFeatureHook()
          {
              if (!self::$hasThrownError) {
                  self::$hasThrownError = true;
                  throw new \Exception('Error in afterFeature hook');
              }
          }

          /**
           * @When I have a simple step
           */
          public function iHaveASimpleStep()
          {
          }
      }
      """
    When I run "behat --no-colors --format=junit --out=junit"
    Then it should fail
    And "junit/default.xml" file xml should be like:
      """
      <?xml version="1.0" encoding="UTF-8"?>
      <testsuites name="default">
        <testsuite name="First feature" tests="2" skipped="0" failures="0" errors="0" time="-IGNORE-VALUE-">
          <testcase name="First scenario" classname="First feature" status="passed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-one.feature"></testcase>
          <testcase name="Second scenario" classname="First feature" status="passed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-one.feature">
            <failure message="AfterFeature: Error in afterFeature hook (Exception)" type="teardown"></failure>
          </testcase>
        </testsuite>
        <testsuite name="Second feature" tests="1" skipped="0" failures="0" errors="0" time="-IGNORE-VALUE-">
          <testcase name="First scenario" classname="Second feature" status="passed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-two.feature"></testcase>
        </testsuite>
      </testsuites>
      """
    And the file "junit/default.xml" should be a valid document according to "junit.xsd"

  Scenario: Handling of a error in beforeScenario hook
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          private static $hasThrownError = false;

          /**
           * @BeforeScenario
           */
          public function beforeScenarioHook()
          {
              if (!self::$hasThrownError) {
                  self::$hasThrownError = true;
                  throw new \Exception('Error in beforeScenario hook');
              }
          }

          /**
           * @When I have a simple step
           */
          public function iHaveASimpleStep()
          {
          }
      }
      """
    When I run "behat --no-colors --format=junit --out=junit"
    Then it should fail
    And "junit/default.xml" file xml should be like:
      """
      <?xml version="1.0" encoding="UTF-8"?>
      <testsuites name="default">
        <testsuite name="First feature" tests="2" skipped="1" failures="0" errors="0" time="-IGNORE-VALUE-">
          <testcase name="First scenario" classname="First feature" status="skipped" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-one.feature">
            <failure message="BeforeScenario: Error in beforeScenario hook (Exception)" type="setup"></failure>
          </testcase>
          <testcase name="Second scenario" classname="First feature" status="passed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-one.feature"></testcase>
        </testsuite>
        <testsuite name="Second feature" tests="1" skipped="0" failures="0" errors="0" time="-IGNORE-VALUE-">
          <testcase name="First scenario" classname="Second feature" status="passed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-two.feature"></testcase>
        </testsuite>
      </testsuites>
      """
    And the file "junit/default.xml" should be a valid document according to "junit.xsd"

  Scenario: Handling of a error in afterScenario hook
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          private static $hasThrownError = false;

          /**
           * @AfterScenario
           */
          public function afterScenarioHook()
          {
              if (!self::$hasThrownError) {
                  self::$hasThrownError = true;
                  throw new \Exception('Error in afterScenario hook');
              }
          }

          /**
           * @When I have a simple step
           */
          public function iHaveASimpleStep()
          {
          }
      }
      """
    When I run "behat --no-colors --format=junit --out=junit"
    Then it should fail
    And "junit/default.xml" file xml should be like:
      """
      <?xml version="1.0" encoding="UTF-8"?>
      <testsuites name="default">
        <testsuite name="First feature" tests="2" skipped="0" failures="0" errors="0" time="-IGNORE-VALUE-">
          <testcase name="First scenario" classname="First feature" status="passed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-one.feature">
            <failure message="AfterScenario: Error in afterScenario hook (Exception)" type="teardown"></failure>
          </testcase>
          <testcase name="Second scenario" classname="First feature" status="passed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-one.feature"></testcase>
        </testsuite>
        <testsuite name="Second feature" tests="1" skipped="0" failures="0" errors="0" time="-IGNORE-VALUE-">
          <testcase name="First scenario" classname="Second feature" status="passed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-two.feature"></testcase>
        </testsuite>
      </testsuites>
      """
    And the file "junit/default.xml" should be a valid document according to "junit.xsd"

  Scenario: Handling of a error in beforeStep hook
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          private static $hasThrownError = false;

          /**
           * @BeforeStep
           */
          public function beforeStepHook()
          {
              if (!self::$hasThrownError) {
                  self::$hasThrownError = true;
                  throw new \Exception('Error in beforeStep hook');
              }
          }

          /**
           * @When I have a simple step
           */
          public function iHaveASimpleStep()
          {
          }
      }
      """
    When I run "behat --no-colors --format=junit --out=junit"
    Then it should fail
    And "junit/default.xml" file xml should be like:
      """
      <?xml version="1.0" encoding="UTF-8"?>
      <testsuites name="default">
        <testsuite name="First feature" tests="2" skipped="0" failures="1" errors="0" time="-IGNORE-VALUE-">
          <testcase name="First scenario" classname="First feature" status="failed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-one.feature">
            <failure message="BeforeStep: When I have a simple step: Error in beforeStep hook (Exception)" type="setup"></failure>
          </testcase>
          <testcase name="Second scenario" classname="First feature" status="passed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-one.feature"></testcase>
        </testsuite>
        <testsuite name="Second feature" tests="1" skipped="0" failures="0" errors="0" time="-IGNORE-VALUE-">
          <testcase name="First scenario" classname="Second feature" status="passed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-two.feature"></testcase>
        </testsuite>
      </testsuites>
      """
    And the file "junit/default.xml" should be a valid document according to "junit.xsd"

  Scenario: Handling of a error in afterStep hook
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          private static $hasThrownError = false;

          /**
           * @AfterStep
           */
          public function afterStepHook()
          {
              if (!self::$hasThrownError) {
                  self::$hasThrownError = true;
                  throw new \Exception('Error in afterStep hook');
              }
          }

          /**
           * @When I have a simple step
           */
          public function iHaveASimpleStep()
          {
          }
      }
      """
    When I run "behat --no-colors --format=junit --out=junit"
    Then it should fail
    And "junit/default.xml" file xml should be like:
      """
      <?xml version="1.0" encoding="UTF-8"?>
      <testsuites name="default">
        <testsuite name="First feature" tests="2" skipped="0" failures="1" errors="0" time="-IGNORE-VALUE-">
          <testcase name="First scenario" classname="First feature" status="failed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-one.feature">
            <failure message="AfterStep: When I have a simple step: Error in afterStep hook (Exception)" type="teardown"></failure>
          </testcase>
          <testcase name="Second scenario" classname="First feature" status="passed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-one.feature"></testcase>
        </testsuite>
        <testsuite name="Second feature" tests="1" skipped="0" failures="0" errors="0" time="-IGNORE-VALUE-">
          <testcase name="First scenario" classname="Second feature" status="passed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-two.feature"></testcase>
        </testsuite>
      </testsuites>
      """
    And the file "junit/default.xml" should be a valid document according to "junit.xsd"
