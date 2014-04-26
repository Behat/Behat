Feature: Empty feature
  In order to follow BDD practice without a hassle
  As a BDD practitioner
  I need to be able to leave scenario titles without steps for time being

  Scenario: Empty scenario
    Given a file named "features/bootstrap/FeatureContext.php" with:
    """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
      }
      """
    And a file named "features/empty_scenario.feature" with:
      """
      Feature:

        Scenario: show error
      """
    When I run "behat --no-colors -f progress"
    Then it should pass with:
      """
      No scenarios
      No steps
      """
