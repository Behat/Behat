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
    And a file named "features/a/feature1.feature" with:
      """
      Feature: First Feature

        Scenario: First Scenario
          Given Some slow step N12
          And Some normal step N13

        Scenario: Second Scenario
          Given Some fast step N14
      """
    And a file named "features/a/feature2.feature" with:
      """
      Feature: Second Feature

        Background:
          Given Some normal step N21

        Scenario: First Scenario
          Given Some slow step N22
          And Some fast step N23
      """
    And a file named "features/b/feature1.feature" with:
      """
      Feature: Third Feature

        Scenario: First Scenario
          Given Some fast step N14

        Scenario: Second Scenario
          Given Some fast step N14
          And Some fast step N14
      """

  Scenario: First feature file only
    When I run "behat --no-colors -f pretty features/a/feature1.feature"
    Then it should pass with:
      """
      Feature: First Feature

        Scenario: First Scenario   # features/a/feature1.feature:3
          Given Some slow step N12 # FeatureContext::someSlowStepN()
          And Some normal step N13 # FeatureContext::someNormalStepN()

        Scenario: Second Scenario  # features/a/feature1.feature:7
          Given Some fast step N14 # FeatureContext::someFastStepN()

      2 scenarios (2 passed)
      3 steps (3 passed)
      """

  Scenario: Second feature file only
    When I run "behat --no-colors -f pretty features/a/feature2.feature"
    Then it should pass with:
      """
      Feature: Second Feature

        Background:                  # features/a/feature2.feature:3
          Given Some normal step N21 # FeatureContext::someNormalStepN()

        Scenario: First Scenario   # features/a/feature2.feature:6
          Given Some slow step N22 # FeatureContext::someSlowStepN()
          And Some fast step N23   # FeatureContext::someFastStepN()

      1 scenario (1 passed)
      3 steps (3 passed)
      """

  Scenario: Both feature files
    When I run "behat --no-colors -f pretty features/a/feature1.feature features/a/feature2.feature"
    Then it should pass with:
      """
      Feature: First Feature

        Scenario: First Scenario   # features/a/feature1.feature:3
          Given Some slow step N12 # FeatureContext::someSlowStepN()
          And Some normal step N13 # FeatureContext::someNormalStepN()

        Scenario: Second Scenario  # features/a/feature1.feature:7
          Given Some fast step N14 # FeatureContext::someFastStepN()

      Feature: Second Feature

        Background:                  # features/a/feature2.feature:3
          Given Some normal step N21 # FeatureContext::someNormalStepN()

        Scenario: First Scenario   # features/a/feature2.feature:6
          Given Some slow step N22 # FeatureContext::someSlowStepN()
          And Some fast step N23   # FeatureContext::someFastStepN()

      3 scenarios (3 passed)
      6 steps (6 passed)
      """

    Scenario: Single nested directory
      When I run "behat --no-colors -f pretty features/a"
      Then it should pass with:
      """
      Feature: First Feature

        Scenario: First Scenario   # features/a/feature1.feature:3
          Given Some slow step N12 # FeatureContext::someSlowStepN()
          And Some normal step N13 # FeatureContext::someNormalStepN()

        Scenario: Second Scenario  # features/a/feature1.feature:7
          Given Some fast step N14 # FeatureContext::someFastStepN()

      Feature: Second Feature

        Background:                  # features/a/feature2.feature:3
          Given Some normal step N21 # FeatureContext::someNormalStepN()

        Scenario: First Scenario   # features/a/feature2.feature:6
          Given Some slow step N22 # FeatureContext::someSlowStepN()
          And Some fast step N23   # FeatureContext::someFastStepN()

      3 scenarios (3 passed)
      6 steps (6 passed)
      """

  Scenario: Directory with nested directories
    When I run "behat --no-colors -f pretty features"
    Then it should pass with:
      """
      Feature: First Feature

        Scenario: First Scenario   # features/a/feature1.feature:3
          Given Some slow step N12 # FeatureContext::someSlowStepN()
          And Some normal step N13 # FeatureContext::someNormalStepN()

        Scenario: Second Scenario  # features/a/feature1.feature:7
          Given Some fast step N14 # FeatureContext::someFastStepN()

      Feature: Second Feature

        Background:                  # features/a/feature2.feature:3
          Given Some normal step N21 # FeatureContext::someNormalStepN()

        Scenario: First Scenario   # features/a/feature2.feature:6
          Given Some slow step N22 # FeatureContext::someSlowStepN()
          And Some fast step N23   # FeatureContext::someFastStepN()

      Feature: Third Feature

        Scenario: First Scenario   # features/b/feature1.feature:3
          Given Some fast step N14 # FeatureContext::someFastStepN()

        Scenario: Second Scenario  # features/b/feature1.feature:6
          Given Some fast step N14 # FeatureContext::someFastStepN()
          And Some fast step N14   # FeatureContext::someFastStepN()

      5 scenarios (5 passed)
      9 steps (9 passed)
      """

    Scenario: Single scenario from single feature file
      When I run "behat --no-colors -f pretty features/a/feature1.feature:7"
      Then it should pass with:
      """
      Feature: First Feature

        Scenario: Second Scenario  # features/a/feature1.feature:7
          Given Some fast step N14 # FeatureContext::someFastStepN()

      1 scenario (1 passed)
      1 step (1 passed)
      """

  Scenario: Single scenario from each feature file
    When I run "behat --no-colors -f pretty features/a/feature1.feature:7 features/a/feature2.feature:6"
    Then it should pass with:
      """
      Feature: First Feature

        Scenario: Second Scenario  # features/a/feature1.feature:7
          Given Some fast step N14 # FeatureContext::someFastStepN()

      Feature: Second Feature

        Background:                  # features/a/feature2.feature:3
          Given Some normal step N21 # FeatureContext::someNormalStepN()

        Scenario: First Scenario   # features/a/feature2.feature:6
          Given Some slow step N22 # FeatureContext::someSlowStepN()
          And Some fast step N23   # FeatureContext::someFastStepN()

      2 scenarios (2 passed)
      4 steps (4 passed)
      """
