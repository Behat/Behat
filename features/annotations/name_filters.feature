Feature: Name filters
  In order to run only needed features
  As a Behat user
  I need to Behat support features & scenario/outline names filtering

  Background:
    Given a file named "features/bootstrap/bootstrap.php" with:
      """
      <?php
      require_once 'PHPUnit/Autoload.php';
      require_once 'PHPUnit/Framework/Assert/Functions.php';
      """
    And a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\BehatContext,
          Behat\Behat\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeatureContext extends BehatContext
      {
          /**
           * @Given /^Some slow step N(\d+)$/
           */
          public function someSlowStepN($num) {}

          /**
           * @Given /^Some normal step N(\d+)$/
           */
          public function someNormalStepN($num) {}

          /**
           * @Given /^Some fast step N(\d+)$/
           */
          public function someFastStepN($num) {}
      }
    """
    And a file named "features/feature1.feature" with:
      """
      Feature: First Feature

        Background:
          Given Some slow step N11

        Scenario: First Scenario
          Given Some slow step N12
          And Some normal step N13

        Scenario: Second Scenario
          Given Some fast step N14
      """
    And a file named "features/feature2.feature" with:
      """
      Feature: Second Feature

        Background:
          Given Some normal step N21

        Scenario: First Scenario
          Given Some slow step N22
          And Some fast step N23
      """

  Scenario: First Name
    When I run "behat --no-ansi -f pretty --name First"
    Then it should pass with:
      """
      Feature: First Feature

        Background:                # features/feature1.feature:3
          Given Some slow step N11 # FeatureContext::someSlowStepN()

        Scenario: First Scenario   # features/feature1.feature:6
          Given Some slow step N12 # FeatureContext::someSlowStepN()
          And Some normal step N13 # FeatureContext::someNormalStepN()

        Scenario: Second Scenario  # features/feature1.feature:10
          Given Some fast step N14 # FeatureContext::someFastStepN()

      Feature: Second Feature

        Background:                  # features/feature2.feature:3
          Given Some normal step N21 # FeatureContext::someNormalStepN()

        Scenario: First Scenario     # features/feature2.feature:6
          Given Some slow step N22   # FeatureContext::someSlowStepN()
          And Some fast step N23     # FeatureContext::someFastStepN()

      3 scenarios (3 passed)
      8 steps (8 passed)
      """

  Scenario: Second Name
    When I run "behat --no-ansi -f pretty --name 'Second Scenario'"
    Then it should pass with:
      """
      Feature: First Feature

        Background:                # features/feature1.feature:3
          Given Some slow step N11 # FeatureContext::someSlowStepN()

        Scenario: Second Scenario  # features/feature1.feature:10
          Given Some fast step N14 # FeatureContext::someFastStepN()

      1 scenario (1 passed)
      2 steps (2 passed)
      """

  Scenario: RegEx
    When I run "behat --no-ansi -f pretty --name '/nd Scenario$/'"
    Then it should pass with:
      """
      Feature: First Feature

        Background:                # features/feature1.feature:3
          Given Some slow step N11 # FeatureContext::someSlowStepN()

        Scenario: Second Scenario  # features/feature1.feature:10
          Given Some fast step N14 # FeatureContext::someFastStepN()

      1 scenario (1 passed)
      2 steps (2 passed)
      """
