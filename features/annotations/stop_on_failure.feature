Feature: Stop on failure
  In order to stop further execution of steps when first step fails
  As a feature developer
  I need to have a --stop-on-failure option

  Background:
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      require_once 'PHPUnit/Autoload.php';
      require_once 'PHPUnit/Framework/Assert/Functions.php';

      use Behat\Behat\Context\BehatContext,
          Behat\Behat\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeatureContext extends BehatContext
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

        Scenario: 2st Failing
          When I have a step that fails
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
    When I run "behat --no-ansi --no-paths --stop-on-failure features/failing.feature"
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
            step failed as supposed
          Then I should have a scenario that failed
      
      3 scenarios (2 passed, 1 failed)
      11 steps (9 passed, 1 skipped, 1 failed)
      """

  Scenario: Just run feature
    When I run "behat --no-ansi --no-paths --stop-on-failure features/passing.feature"
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

