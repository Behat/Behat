Feature: Config
  In order to configure behat for my needs
  As a feature automator
  I need to be able to use behat configuration file

  Scenario: Minimal configuration file
    Given a file named "behat.php" with:
      """
      <?php

      use Behat\Config\Config;

      return new Config();
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
    When I run "behat -f progress --no-colors --append-snippets --config=missing-behat.php"
    Then it should fail with:
      """
      The requested config file does not exist
      """

  Scenario: Alternative PHP configuration file
    Given a file named "alternative-behat.php" with:
      """
      <?php

      use Behat\Config\Config;

      return new Config(['default' => ['formatters' => ['progress' => true]]]);

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
    When I run "behat --no-colors --append-snippets --config=alternative-behat.php"
    Then it should pass with:
      """
      U

      1 scenario (1 undefined)
      1 step (1 undefined)

      --- Use --snippets-for CLI option to generate snippets for following default suite steps:

          When this scenario executes
      """

  Scenario: Custom PHP configuration object
    Given a file named "custom-config-object.php" with:
      """
      <?php

      use Behat\Config\ConfigInterface;

      final class CustomConfig implements ConfigInterface
      {
        public function toArray(): array
        {
          return [
            'default' => [
              'testers' => ['strict' => true],
            ],
          ];
        }
      }

      return new CustomConfig();

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
        @first
        Scenario:
          When this first scenario executes

        @second
        Scenario:
          When this second scenario executes
      """
    When I run "behat -f progress --no-colors -n --config=custom-config-object.php"
    Then it should fail with:
      """
      UU

      2 scenarios (2 undefined)
      2 steps (2 undefined)

      --- Use --snippets-for CLI option to generate snippets for following default suite steps:

          When this first scenario executes
          When this second scenario executes
      """

  Scenario: Load custom php config instead of distribution
    Given a file named "behat.php"
    Given a file named "behat.dist.php"
    Given a some feature context
    And a some feature scenarios
    When I run behat in debug mode
    Then the output should contain:
      """
      behat.php
      """
