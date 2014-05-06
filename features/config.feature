Feature: Config
  In order to configure behat for my needs
  As a feature automator
  I need to be able to use behat configuration file

  Scenario: Empty configuration file
    Given a file named "behat.yml" with:
      """
      """
    And a file named "features/bootstrap/FeatureContext.php" with:
    """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
      }
      """
    And a file named "features/config.feature" with:
    """
      Feature:
        Scenario:
          When this scenario executes
      """
    When I run "behat -f progress --append-snippets"
    Then it should pass with:
      """
      U

      1 scenario (1 undefined)
      1 step (1 undefined)

      --- Snippets for the following steps in the default suite were not generated (check your configuration):

          When this scenario executes
      """
