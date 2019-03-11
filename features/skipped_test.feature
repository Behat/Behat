Feature: Skipped tests
  In order to allow some tests to be excluded
  As a features automator
  I can exclude tests by my own criteria

  Background:
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          /**
           * @Given /^I have (?:a|another) step that passes?$/
           * @Then /^I should have a scenario that passed$/
           */
          public function passing() {
          }

          /**
           * @Given /^I have (?:a|another) step that fails?$/
           * @Then /^I should have a scenario that failed$/
           */
          public function failed() {
              throw new Exception("step failed as supposed");
          }

          /**
           * @BeforeScenario @skippedScenario
           */
          public function skippedScenario() {
              throw new Exception("This Scenario should be skipped");
          }

          /**
          * @BeforeFeature @skippedFeature
           */
          static public function skippedFeature() {
              throw new Exception("This Feature should be skipped");
          }

      }
      """
    And a file named "features/skipped_feature.feature" with:
      """
      @skippedFeature
      Feature: Feature with a skipped scenario
        In order to test the Scenario Skipping feature
        As a behat developer
        I need to have a scenario that fails

        Scenario: 1st Passing
          When I have a step that passes
          Then I should have a scenario that passed

        Scenario: 2nd Passing
          When I have a step that passes
           And I have another step that passes
          Then I should have a scenario that passed

        Scenario: 1st Skipped
          When I have a step that fails
          Then I should have a scenario that failed
      """
    And a file named "features/skipped_scenario.feature" with:
      """
      Feature: Feature with a skipped scenario
        In order to test the Scenario Skipping feature
        As a behat developer
        I need to have a scenario that fails

        Scenario: 1st Passing
          When I have a step that passes
          Then I should have a scenario that passed

        Scenario: 2nd Passing
          When I have a step that passes
           And I have another step that passes
          Then I should have a scenario that passed

        @skippedScenario
        Scenario: 1st Skipped
          When I have a step that fails
          Then I should have a scenario that failed
      """

  Scenario: Run feature containing a skipped scenario interpretting the results softly
    When I run "behat --no-colors --format-settings='{\"paths\": false}' features/skipped_scenario.feature"
    Then it should pass with:
      """
      Feature: Feature with a skipped scenario
        In order to test the Scenario Skipping feature
        As a behat developer
        I need to have a scenario that fails

        Scenario: 1st Passing
          When I have a step that passes
          Then I should have a scenario that passed

        Scenario: 2nd Passing
          When I have a step that passes
          And I have another step that passes
          Then I should have a scenario that passed

        ┌─ @BeforeScenario @skippedScenario # FeatureContext::skippedScenario()
        │
        ╳  This Scenario should be skipped (Exception)
        │
        @skippedScenario
        Scenario: 1st Skipped
          When I have a step that fails
          Then I should have a scenario that failed

      --- Skipped scenarios:

          features/skipped_scenario.feature:16

      3 scenarios (2 passed, 1 skipped)
      7 steps (5 passed, 2 skipped)
      """

  Scenario: Run feature containing a skipped scenario interpretting the results strictly
    When I run "behat --no-colors --format-settings='{\"paths\": false}' features/skipped_scenario.feature --strict"
    Then it should fail with:
      """
      Feature: Feature with a skipped scenario
        In order to test the Scenario Skipping feature
        As a behat developer
        I need to have a scenario that fails

        Scenario: 1st Passing
          When I have a step that passes
          Then I should have a scenario that passed

        Scenario: 2nd Passing
          When I have a step that passes
          And I have another step that passes
          Then I should have a scenario that passed

        ┌─ @BeforeScenario @skippedScenario # FeatureContext::skippedScenario()
        │
        ╳  This Scenario should be skipped (Exception)
        │
        @skippedScenario
        Scenario: 1st Skipped
          When I have a step that fails
          Then I should have a scenario that failed

      --- Skipped scenarios:

          features/skipped_scenario.feature:16

      3 scenarios (2 passed, 1 skipped)
      7 steps (5 passed, 2 skipped)
      """

  Scenario: Run skipped feature interpretting the results softly
    When I run "behat --no-colors --format-settings='{\"paths\": false}' features/skipped_feature.feature"
    Then it should pass with:
      """
      ┌─ @BeforeFeature @skippedFeature # FeatureContext::skippedFeature()
      │
      ╳  This Feature should be skipped (Exception)
      │
      @skippedFeature
      Feature: Feature with a skipped scenario
        In order to test the Scenario Skipping feature
        As a behat developer
        I need to have a scenario that fails

        Scenario: 1st Passing
          When I have a step that passes
          Then I should have a scenario that passed

        Scenario: 2nd Passing
          When I have a step that passes
          And I have another step that passes
          Then I should have a scenario that passed

        Scenario: 1st Skipped
          When I have a step that fails
          Then I should have a scenario that failed

      --- Skipped scenarios:

          features/skipped_feature.feature:7
          features/skipped_feature.feature:11
          features/skipped_feature.feature:16
      """

  Scenario: Run skipped feature interpretting the results strictly
    When I run "behat --no-colors --format-settings='{\"paths\": false}' features/skipped_feature.feature --strict"
    Then it should fail with:
      """
      ┌─ @BeforeFeature @skippedFeature # FeatureContext::skippedFeature()
      │
      ╳  This Feature should be skipped (Exception)
      │
      @skippedFeature
      Feature: Feature with a skipped scenario
        In order to test the Scenario Skipping feature
        As a behat developer
        I need to have a scenario that fails

        Scenario: 1st Passing
          When I have a step that passes
          Then I should have a scenario that passed

        Scenario: 2nd Passing
          When I have a step that passes
          And I have another step that passes
          Then I should have a scenario that passed

        Scenario: 1st Skipped
          When I have a step that fails
          Then I should have a scenario that failed

      --- Skipped scenarios:

          features/skipped_feature.feature:7
          features/skipped_feature.feature:11
          features/skipped_feature.feature:16
      """
