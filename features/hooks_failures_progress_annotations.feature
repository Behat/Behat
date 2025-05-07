Feature: Display hook failures location in progress printer using annotations
  In order to be able to locate the code that generated a failure
  As a feature developer using the progress printer
  When a hook throws an error I want to see the related item where the code failed using annotations

  Background:
    Given a file named "features/simple.feature" with:
      """
      Feature: Simple feature

        Scenario: Simple scenario
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
