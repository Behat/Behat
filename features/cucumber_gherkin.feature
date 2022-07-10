@cucumber-parser
Feature: Cucumber gherkin parser can add new features

  Background:
    Given a file named "behat.yml" with:
      """
      default:
        gherkin:
          parser: cucumber
      """

  Scenario: Using the Rules keyword

    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          /** @Given passing  */
          function passing() {}
      }
      """
    And a file named "features/config.feature" with:
      """
      Feature:

        Scenario:
          Given passing

        Rule:

          Scenario:
            Given passing
      """
    When I run "behat -f progress --no-colors"
    Then it should pass with:
      """
      2 scenarios (2 passed)
      2 steps (2 passed)
      """

  Scenario: Running rules by tag

    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          /** @Given passing */
          function passing() {}
      }
      """
    And a file named "features/config.feature" with:
      """
      Feature:

        @foo
        Rule:

          Scenario:
            Given passing
      """
    When I run "behat -f progress --no-colors --tags=foo"
    Then it should pass with:
      """
      1 scenario (1 passed)
      1 step (1 passed)
      """
