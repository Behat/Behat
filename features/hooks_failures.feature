Feature: Display hook failures location in progress printer
  In order to be able to locate the code that generated a failure
  As a feature developer using the progress printer
  When a hook throws an error I want to see the related item where the code failed

  Background:
    Given a file named "features/simple.feature" with:
      """
      Feature: Simple feature

        Scenario: Simple scenario
          When I have a simple step
      """

  #progress format
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
      }
      """
    When I run "behat --no-colors --format=progress"
    Then it should fail
    And the output should contain:
      """
      BeforeSuite "default" # FeatureContext::beforeSuiteHook()
      """

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
      }
      """
    When I run "behat --no-colors --format=progress"
    Then it should fail
    And the output should contain:
      """
      AfterSuite "default" # FeatureContext::afterSuiteHook()
      """

  Scenario: Handling of a error in beforeFeature hook
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          /**
           * @BeforeFeature
           */
          public static function beforeFeatureHook()
          {
              throw new \Exception('Error in beforeFeature hook');
          }
      }
      """
    When I run "behat --no-colors --format=progress"
    Then it should fail
    And the output should contain:
      """
      BeforeFeature "features/simple.feature" # FeatureContext::beforeFeatureHook()
      """

  Scenario: Handling of a error in afterFeature hook
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          /**
           * @AfterFeature
           */
          public static function afterFeatureHook()
          {
              throw new \Exception('Error in afterFeature hook');
          }
      }
      """
    When I run "behat --no-colors --format=progress"
    Then it should fail
    And the output should contain:
      """
      AfterFeature "features/simple.feature" # FeatureContext::afterFeatureHook()
      """

  Scenario: Handling of a error in beforeScenario hook
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          /**
           * @BeforeScenario
           */
          public function beforeScenarioHook()
          {
              throw new \Exception('Error in beforeScenario hook');
          }
      }
      """
    When I run "behat --no-colors --format=progress"
    Then it should fail
    And the output should contain:
      """
      BeforeScenario "features/simple.feature:3" # FeatureContext::beforeScenarioHook()
      """

  Scenario: Handling of a error in afterScenario hook
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          /**
           * @AfterScenario
           */
          public function afterScenarioHook()
          {
              throw new \Exception('Error in afterScenario hook');
          }
      }
      """
    When I run "behat --no-colors --format=progress"
    Then it should fail
    And the output should contain:
      """
      AfterScenario "features/simple.feature:3" # FeatureContext::afterScenarioHook()
      """

  Scenario: Handling of a error in beforeStep hook
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          /**
           * @BeforeStep
           */
          public function beforeStepHook()
          {
              throw new \Exception('Error in beforeStep hook');
          }
      }
      """
    When I run "behat --no-colors --format=progress"
    Then it should fail
    And the output should contain:
      """
      BeforeStep "features/simple.feature:4" # FeatureContext::beforeStepHook()
      """

  Scenario: Handling of a error in afterStep hook
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          /**
           * @AfterStep
           */
          public function afterStepHook()
          {
              throw new \Exception('Error in afterStep hook');
          }
      }
      """
    When I run "behat --no-colors --format=progress"
    Then it should fail
    And the output should contain:
      """
      AfterStep "features/simple.feature:4" # FeatureContext::afterStepHook()
      """

  #pretty format
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
      }
      """
    When I run "behat --no-colors --format=pretty"
    Then it should fail
    And the output should contain:
      """
      BeforeSuite "default" # FeatureContext::beforeSuiteHook()
      """

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
      }
      """
    When I run "behat --no-colors --format=pretty"
    Then it should fail
    And the output should contain:
      """
      AfterSuite "default" # FeatureContext::afterSuiteHook()
      """

  Scenario: Handling of a error in beforeFeature hook
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          /**
           * @BeforeFeature
           */
          public static function beforeFeatureHook()
          {
              throw new \Exception('Error in beforeFeature hook');
          }
      }
      """
    When I run "behat --no-colors --format=pretty"
    Then it should fail
    And the output should contain:
      """
      BeforeFeature "features/simple.feature" # FeatureContext::beforeFeatureHook()
      """

  Scenario: Handling of a error in afterFeature hook
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          /**
           * @AfterFeature
           */
          public static function afterFeatureHook()
          {
              throw new \Exception('Error in afterFeature hook');
          }
      }
      """
    When I run "behat --no-colors --format=pretty"
    Then it should fail
    And the output should contain:
      """
      AfterFeature "features/simple.feature" # FeatureContext::afterFeatureHook()
      """

  Scenario: Handling of a error in beforeScenario hook
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          /**
           * @BeforeScenario
           */
          public function beforeScenarioHook()
          {
              throw new \Exception('Error in beforeScenario hook');
          }
      }
      """
    When I run "behat --no-colors --format=pretty"
    Then it should fail
    And the output should contain:
      """
      BeforeScenario "features/simple.feature:3" # FeatureContext::beforeScenarioHook()
      """

  Scenario: Handling of a error in afterScenario hook
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          /**
           * @AfterScenario
           */
          public function afterScenarioHook()
          {
              throw new \Exception('Error in afterScenario hook');
          }
      }
      """
    When I run "behat --no-colors --format=pretty"
    Then it should fail
    And the output should contain:
      """
      AfterScenario "features/simple.feature:3" # FeatureContext::afterScenarioHook()
      """

  Scenario: Handling of a error in beforeStep hook
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          /**
           * @BeforeStep
           */
          public function beforeStepHook()
          {
              throw new \Exception('Error in beforeStep hook');
          }
      }
      """
    When I run "behat --no-colors --format=pretty"
    Then it should fail
    And the output should contain:
      """
      BeforeStep "features/simple.feature:4" # FeatureContext::beforeStepHook()
      """

  Scenario: Handling of a error in afterStep hook
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          /**
           * @AfterStep
           */
          public function afterStepHook()
          {
              throw new \Exception('Error in afterStep hook');
          }
      }
      """
    When I run "behat --no-colors --format=pretty"
    Then it should fail
    And the output should contain:
      """
      AfterStep "features/simple.feature:4" # FeatureContext::afterStepHook()
      """

  #junit format
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
        <testsuite name="Simple feature" tests="1" skipped="1" failures="0" errors="0" time="-IGNORE-VALUE-">
          <testcase name="Simple scenario" classname="Simple feature" status="skipped" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-simple.feature">
            <failure message="BeforeSuite: Error in beforeSuite hook (Exception)" type="setup"></failure>
          </testcase>
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
        <testsuite name="Simple feature" tests="1" skipped="0" failures="0" errors="0" time="-IGNORE-VALUE-">
          <testcase name="Simple scenario" classname="Simple feature" status="passed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-simple.feature">
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
          /**
           * @BeforeFeature
           */
          public static function beforeFeatureHook()
          {
              throw new \Exception('Error in beforeFeature hook');
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
        <testsuite name="Simple feature" tests="1" skipped="1" failures="0" errors="0" time="-IGNORE-VALUE-">
          <testcase name="Simple scenario" classname="Simple feature" status="skipped" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-simple.feature">
            <failure message="BeforeFeature: Error in beforeFeature hook (Exception)" type="setup"></failure>
          </testcase>
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
          /**
           * @AfterFeature
           */
          public static function afterFeatureHook()
          {
              throw new \Exception('Error in afterFeature hook');
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
        <testsuite name="Simple feature" tests="1" skipped="0" failures="0" errors="0" time="-IGNORE-VALUE-">
          <testcase name="Simple scenario" classname="Simple feature" status="passed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-simple.feature">
            <failure message="AfterFeature: Error in afterFeature hook (Exception)" type="teardown"></failure>
          </testcase>
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
          /**
           * @BeforeScenario
           */
          public function beforeScenarioHook()
          {
              throw new \Exception('Error in beforeScenario hook');
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
        <testsuite name="Simple feature" tests="1" skipped="1" failures="0" errors="0" time="-IGNORE-VALUE-">
          <testcase name="Simple scenario" classname="Simple feature" status="skipped" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-simple.feature">
            <failure message="BeforeScenario: Error in beforeScenario hook (Exception)" type="setup"></failure>
          </testcase>
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
          /**
           * @AfterScenario
           */
          public function afterScenarioHook()
          {
              throw new \Exception('Error in afterScenario hook');
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
        <testsuite name="Simple feature" tests="1" skipped="0" failures="0" errors="0" time="-IGNORE-VALUE-">
          <testcase name="Simple scenario" classname="Simple feature" status="passed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-simple.feature">
            <failure message="AfterScenario: Error in afterScenario hook (Exception)" type="teardown"></failure>
          </testcase>
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
          /**
           * @BeforeStep
           */
          public function beforeStepHook()
          {
              throw new \Exception('Error in beforeStep hook');
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
        <testsuite name="Simple feature" tests="1" skipped="0" failures="1" errors="0" time="-IGNORE-VALUE-">
          <testcase name="Simple scenario" classname="Simple feature" status="failed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-simple.feature">
            <failure message="BeforeStep: When I have a simple step: Error in beforeStep hook (Exception)" type="setup"></failure>
          </testcase>
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
          /**
           * @AfterStep
           */
          public function afterStepHook()
          {
              throw new \Exception('Error in afterStep hook');
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
        <testsuite name="Simple feature" tests="1" skipped="0" failures="1" errors="0" time="-IGNORE-VALUE-">
          <testcase name="Simple scenario" classname="Simple feature" status="failed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-simple.feature">
            <failure message="AfterStep: When I have a simple step: Error in afterStep hook (Exception)" type="teardown"></failure>
          </testcase>
        </testsuite>
      </testsuites>
      """
    And the file "junit/default.xml" should be a valid document according to "junit.xsd"
