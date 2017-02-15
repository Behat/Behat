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
    When I run "behat -f progress --no-colors --append-snippets"
    Then it should pass with:
      """
      U

      1 scenario (1 undefined)
      1 step (1 undefined)

      --- Use --snippets-for CLI option to generate snippets for following default suite steps:

          When this scenario executes
      """

  Scenario: Alternative configuration file
    Given a file named "alternative-behat.yml" with:
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
    When I run "behat -f progress --no-colors --append-snippets --config=alternative-behat.yml"
    Then it should pass with:
      """
      U

      1 scenario (1 undefined)
      1 step (1 undefined)

      --- Use --snippets-for CLI option to generate snippets for following default suite steps:

          When this scenario executes
      """

  Scenario: Alternative configuration file could not be found
    Given a file named "features/bootstrap/FeatureContext.php" with:
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
    When I run "behat -f progress --no-colors --append-snippets --config=missing-behat.yml"
    Then it should fail with:
      """
      The requested config file does not exist
      """

  Scenario: Prioritize *.yaml config file
    Given a file named "behat.yaml"
    Given a file named "behat.yml"
    Given a some feature context
    And a some feature scenarios
    When I run behat in debug mode
    Then the output should contain:
      """
      behat.yaml
      """

  Scenario: Load custom config instead of distribution
    Given a file named "behat.yml"
    Given a file named "behat.yaml.dist"
    Given a some feature context
    And a some feature scenarios
    When I run behat in debug mode
    Then the output should contain:
      """
      behat.yml
      """

  Scenario: Prioritize config file from root
    Given a file named "behat.yaml.dist"
    Given a file named "config/behat.yaml"
    Given a some feature context
    And a some feature scenarios
    When I run behat in debug mode
    Then the output should contain:
      """
      behat.yaml.dist
      """