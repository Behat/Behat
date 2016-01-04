Feature: Empty background
  In order to follow BDD practice without a hassle
  As a BDD practitioner
  I need to be able to leave background definition without steps

  Scenario: Empty background
    Given a file named "features/bootstrap/FeatureContext.php" with:
    """
    <?php

    use Behat\Behat\Context\Context;

    class FeatureContext implements Context
    {
        /** @When this scenario executes */
        public function thisScenarioExecutes() {}
    }
    """
    And a file named "features/empty_background.feature" with:
    """
    Feature: Empty background
      Background:

      Scenario: scenario 1
        When this scenario executes
    """
    When I run "behat --no-colors -f progress"
    Then it should pass with:
    """
    1 scenario (1 passed)
    """
