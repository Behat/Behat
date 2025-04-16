Feature: Path filters
  In order to run only needed features
  As a Behat user
  I need Behat to support path(s) filtering

  Background:
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;
      use Behat\Step\Given;

      class FeatureContext implements Context
      {
          #[Given('/^Some slow step N(\d+)$/')]
          public function someSlowStepN($num) {}

          #[Given('/^Some normal step N(\d+)$/')]
          public function someNormalStepN($num) {}

          #[Given('/^Some fast step N(\d+)$/')]
          public function someFastStepN($num) {}
      }
     """
    And a file named "features/feature1.feature" with:
      """
      Feature: First Feature

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

  Scenario: First feature file only
    When I run "behat --no-colors -f pretty features/feature1.feature"
    Then it should pass with:
      """
      Feature: First Feature

        Scenario: First Scenario   # features/feature1.feature:3
          Given Some slow step N12 # FeatureContext::someSlowStepN()
          And Some normal step N13 # FeatureContext::someNormalStepN()

        Scenario: Second Scenario  # features/feature1.feature:7
          Given Some fast step N14 # FeatureContext::someFastStepN()

      2 scenarios (2 passed)
      3 steps (3 passed)
      """

  Scenario: Second feature file only
    When I run "behat --no-colors -f pretty features/feature2.feature"
    Then it should pass with:
      """
      Feature: Second Feature

        Background:                  # features/feature2.feature:3
          Given Some normal step N21 # FeatureContext::someNormalStepN()

        Scenario: First Scenario   # features/feature2.feature:6
          Given Some slow step N22 # FeatureContext::someSlowStepN()
          And Some fast step N23   # FeatureContext::someFastStepN()

      1 scenario (1 passed)
      3 steps (3 passed)
      """

  Scenario: Both feature files
    When I run "behat --no-colors -f pretty features/feature1.feature features/feature2.feature"
    Then it should pass with:
      """
      Feature: First Feature

        Scenario: First Scenario   # features/feature1.feature:3
          Given Some slow step N12 # FeatureContext::someSlowStepN()
          And Some normal step N13 # FeatureContext::someNormalStepN()

        Scenario: Second Scenario  # features/feature1.feature:7
          Given Some fast step N14 # FeatureContext::someFastStepN()

      Feature: Second Feature

        Background:                  # features/feature2.feature:3
          Given Some normal step N21 # FeatureContext::someNormalStepN()

        Scenario: First Scenario   # features/feature2.feature:6
          Given Some slow step N22 # FeatureContext::someSlowStepN()
          And Some fast step N23   # FeatureContext::someFastStepN()

      3 scenarios (3 passed)
      6 steps (6 passed)
      """
