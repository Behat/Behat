Feature: Display hook failures location in pretty printer using attributes
  In order to be able to locate the code that generated a failure
  As a feature developer using the pretty printer
  When a hook throws an error I want to see the related item where the code failed using attributes

  Background:
    Given I initialise the working directory from the "HookFailures" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value  |
      | --no-colors |        |
      | --format    | pretty |

  Scenario: Handling of a error in beforeSuite hook
    When I run behat with the following additional options:
      | option    | value       |
      | --profile | beforeSuite |
    Then it should fail
    And the output should contain:
      """
      BeforeSuite "default" # BeforeSuiteContext::beforeSuiteHook()
      """

  Scenario: Handling of a error in afterSuite hook
    When I run behat with the following additional options:
      | option    | value      |
      | --profile | afterSuite |
    Then it should fail
    And the output should contain:
      """
      AfterSuite "default" # AfterSuiteContext::afterSuiteHook()
      """

  Scenario: Handling of a error in beforeFeature hook
    When I run behat with the following additional options:
      | option    | value         |
      | --profile | beforeFeature |
    Then it should fail
    And the output should contain:
      """
      BeforeFeature "features/one.feature" # BeforeFeatureContext::beforeFeatureHook()
      """

  Scenario: Handling of a error in afterFeature hook
    When I run behat with the following additional options:
      | option    | value        |
      | --profile | afterFeature |
    Then it should fail
    And the output should contain:
      """
      AfterFeature "features/one.feature" # AfterFeatureContext::afterFeatureHook()
      """

  Scenario: Handling of a error in beforeScenario hook
    When I run behat with the following additional options:
      | option    | value          |
      | --profile | beforeScenario |
    Then it should fail
    And the output should contain:
      """
      BeforeScenario "features/one.feature:3" # BeforeScenarioContext::beforeScenarioHook()
      """

  Scenario: Handling of a error in afterScenario hook
    When I run behat with the following additional options:
      | option    | value         |
      | --profile | afterScenario |
    Then it should fail
    And the output should contain:
      """
      AfterScenario "features/one.feature:3" # AfterScenarioContext::afterScenarioHook()
      """

  Scenario: Handling of a error in beforeStep hook
    When I run behat with the following additional options:
      | option    | value      |
      | --profile | beforeStep |
    Then it should fail
    And the output should contain:
      """
      BeforeStep "features/one.feature:4" # BeforeStepContext::beforeStepHook()
      """

  Scenario: Handling of a error in afterStep hook
    When I run behat with the following additional options:
      | option    | value     |
      | --profile | afterStep |
    Then it should fail
    And the output should contain:
      """
      AfterStep "features/one.feature:4" # AfterStepContext::afterStepHook()
      """
