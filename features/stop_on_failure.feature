Feature: Stop on failure
  In order to stop further execution of steps when first step fails
  As a feature developer
  I need to have a --stop-on-failure option

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
          public function failing() {
              throw new Exception("step failed as supposed");
          }

      }
      """
    And a file named "features/failing.feature" with:
      """
      Feature: Failing Feature
        In order to test the stop-on-failure feature
        As a behat developer
        I need to have a feature that fails

        Background:
          Given I have a step that passes

        Scenario: 1st Passing
          When I have a step that passes
          Then I should have a scenario that passed

        Scenario: 2nd Passing
          When I have a step that passes
           And I have another step that passes
          Then I should have a scenario that passed

        Scenario: 1st Failing
          When I have a step that passes
           And I have another step that fails
          Then I should have a scenario that failed

        Scenario: 2nd Failing
          When I have a step that fails
          Then I should have a scenario that failed
      """
    And a file named "features/missing-step.feature" with:
      """
      Feature: Missing Step Feature
        In order to test the stop-on-failure and strict features
        As a behat developer
        I need to have a feature with a missing step

        Background:
          Given I have a step that passes

        Scenario: 1st Passing
          When I have a step that passes
          Then I should have a scenario that passed

        Scenario: 2nd Passing
          When I have a step that passes
           And I have another step that passes
          Then I should have a scenario that passed

        Scenario: 1st Failing
          When I have a step that passes
           And I have another step that is missing
          Then I should have a scenario that failed

        Scenario: 2nd Failing
          When I have a step that is missing
          Then I should have a scenario that failed
      """
    And a file named "features/passing.feature" with:
      """
      Feature: Passing Feature
        In order to test the stop-on-failure feature
        As a behat developer
        I need to have a broken feature

        Background:
          Given I have a step that passes

        Scenario: 1st Passing
          When I have a step that passes
          Then I should have a scenario that passed

        Scenario: 2nd Passing
          When I have a step that passes
           And I have another step that passes
          Then I should have a scenario that passed

        Scenario: 3rd Passing
          When I have a step that passes
           And I have another step that passes
           And I have another step that passes
          Then I should have a scenario that passed
      """

  Scenario: Just run feature
    When I run "behat --no-colors --format-settings='{\"paths\": false}' --stop-on-failure features/failing.feature"
    Then it should fail with:
      """
      Feature: Failing Feature
        In order to test the stop-on-failure feature
        As a behat developer
        I need to have a feature that fails
      
        Background:
          Given I have a step that passes
      
        Scenario: 1st Passing
          When I have a step that passes
          Then I should have a scenario that passed
      
        Scenario: 2nd Passing
          When I have a step that passes
          And I have another step that passes
          Then I should have a scenario that passed
      
        Scenario: 1st Failing
          When I have a step that passes
          And I have another step that fails
            step failed as supposed (Exception)
          Then I should have a scenario that failed

      --- Failed scenarios:

          features/failing.feature:18

      3 scenarios (2 passed, 1 failed)
      11 steps (9 passed, 1 failed, 1 skipped)
      """

  Scenario: Just run feature
    When I run "behat --no-colors --format-settings='{\"paths\": false}' --strict --stop-on-failure features/missing-step.feature"
    Then it should fail with:
      """
      Feature: Missing Step Feature
        In order to test the stop-on-failure and strict features
        As a behat developer
        I need to have a feature with a missing step

        Background:
          Given I have a step that passes

        Scenario: 1st Passing
          When I have a step that passes
          Then I should have a scenario that passed

        Scenario: 2nd Passing
          When I have a step that passes
          And I have another step that passes
          Then I should have a scenario that passed

        Scenario: 1st Failing
          When I have a step that passes
          And I have another step that is missing
          Then I should have a scenario that failed

      3 scenarios (2 passed, 1 undefined)
      11 steps (9 passed, 1 undefined, 1 skipped)

      --- Use --snippets-for CLI option to generate snippets for following default suite steps:

          And I have another step that is missing
      """

  Scenario: Just run feature
    When I run "behat --no-colors --format-settings='{\"paths\": false}' --stop-on-failure features/passing.feature"
    Then it should pass with:
      """
      Feature: Passing Feature
        In order to test the stop-on-failure feature
        As a behat developer
        I need to have a broken feature
      
        Background:
          Given I have a step that passes
      
        Scenario: 1st Passing
          When I have a step that passes
          Then I should have a scenario that passed
      
        Scenario: 2nd Passing
          When I have a step that passes
          And I have another step that passes
          Then I should have a scenario that passed
      
        Scenario: 3rd Passing
          When I have a step that passes
          And I have another step that passes
          And I have another step that passes
          Then I should have a scenario that passed
      
      3 scenarios (3 passed)
      12 steps (12 passed)
      """
