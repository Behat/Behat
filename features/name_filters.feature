Feature: Name filters
  In order to run only needed features
  As a Behat user
  I need Behat to support features & scenario/outline names filtering

  Background:
  Given I initialise the working directory from the "NameFilters" fixtures folder
  And I provide the following options for all behat invocations:
    | option      | value |
    | --no-colors |       |

Scenario: First Name
    When I run "behat --name First"
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

        Scenario: First Scenario   # features/feature2.feature:6
          Given Some slow step N22 # FeatureContext::someSlowStepN()
          And Some fast step N23   # FeatureContext::someFastStepN()

      3 scenarios (3 passed)
      8 steps (8 passed)
      """

  Scenario: Second Name
    When I run "behat --name 'Second Scenario'"
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
    When I run "behat --name '/nd Scenario$/'"
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
