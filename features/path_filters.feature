Feature: Path filters
  In order to run only needed features
  As a Behat user
  I need Behat to support path(s) filtering

  Background:
    Given I set the working directory to the "PathFilters" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value   |
      | --no-colors |         |
      | --format    | pretty  |

  Scenario: First feature file only
    When I run "behat features/a/feature1.feature"
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
    When I run "behat features/a/feature2.feature"
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
    When I run "behat features/a/feature1.feature features/a/feature2.feature"
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
      When I run "behat features/a"
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
    When I run "behat features"
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
      When I run "behat features/a/feature1.feature:7"
      Then it should pass with:
      """
      Feature: First Feature

        Scenario: Second Scenario  # features/a/feature1.feature:7
          Given Some fast step N14 # FeatureContext::someFastStepN()

      1 scenario (1 passed)
      1 step (1 passed)
      """

  Scenario: Single scenario from each feature file
    When I run "behat features/a/feature1.feature:7 features/a/feature2.feature:6"
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
