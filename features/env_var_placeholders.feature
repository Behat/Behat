Feature: Symfony Env Var Placeholders
  In order to support different setups
  As a tester
  I need to be able to use environment variables in the behat.yml configuration file

  Background:
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          private $value;

          public function __construct($value) {
              $this->value = $value;
          }

          /**
           * @Then /the value should be configured as "([^"]+)"/
           */
          public function theValueShouldBeConfiguredAs($expected) {
              PHPUnit\Framework\Assert::assertEquals($expected, $this->value);
          }
      }
      """
    And a file named "features/env_var.feature" with:
      """
      Feature: Environment variables

        Scenario:
          Then the value should be configured as "some environment variable value"
      """
    And a file named "behat.yml" with:
      """
      default:
        suites:
          default:
            contexts:
              - FeatureContext:
                - '%env(MY_ENV_VAR)%'
      """

  Scenario:
    When the "MY_ENV_VAR" environment variable is set to "some environment variable value"
    And I run "behat --no-colors"
    Then it should pass with:
      """
      1 scenario (1 passed)
      """
