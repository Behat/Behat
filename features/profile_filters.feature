Feature: Filters
  In order to run only needed features
  As a Behat user
  I need to be able to use gherkin filters

  Background:
    Given I initialise the working directory from the "ProfileFilters" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value |
      | --no-colors |       |

  Scenario: Tag filters
    When I run "behat --config=behat-tag-filter.php"
    Then it should pass with:
      """
      @tag2
      Feature: Second feature
        In order to ...
        As a second user
        I need to ...

        Background:                # features/feature2.feature:7
          Given Some slow step N11 # FeatureContext::someSlowStepN()

        Scenario:                  # features/feature2.feature:10
          Given Some slow step N12 # FeatureContext::someSlowStepN()
          And Some normal step N13 # FeatureContext::someNormalStepN()

        Scenario:                  # features/feature2.feature:14
          Given Some fast step N14 # FeatureContext::someFastStepN()

      @tag2
      Feature: A bit less simple feature
        In order to ...
        As a third user
        I need to ...

        Background:                # features/feature3.feature:7
          Given Some slow step N11 # FeatureContext::someSlowStepN()

        Scenario:                  # features/feature3.feature:10
          Given Some slow step N12 # FeatureContext::someSlowStepN()
          And Some normal step N13 # FeatureContext::someNormalStepN()

        Scenario:                  # features/feature3.feature:14
          Given Some fast step N14 # FeatureContext::someFastStepN()

      4 scenarios (4 passed)
      10 steps (10 passed)
      """

  Scenario: Role filters
    When I run "behat --config=behat-role-filter.php"
    Then it should pass with:
      """
      @tag2
      Feature: Second feature
        In order to ...
        As a second user
        I need to ...

        Background:                # features/feature2.feature:7
          Given Some slow step N11 # FeatureContext::someSlowStepN()

        Scenario:                  # features/feature2.feature:10
          Given Some slow step N12 # FeatureContext::someSlowStepN()
          And Some normal step N13 # FeatureContext::someNormalStepN()

        Scenario:                  # features/feature2.feature:14
          Given Some fast step N14 # FeatureContext::someFastStepN()

      2 scenarios (2 passed)
      5 steps (5 passed)
      """

  Scenario: Narrative filters
    When I run "behat --config=behat-narrative-filter.php"
    Then it should pass with:
      """
      @tag2
      Feature: Second feature
        In order to ...
        As a second user
        I need to ...

        Background:                # features/feature2.feature:7
          Given Some slow step N11 # FeatureContext::someSlowStepN()

        Scenario:                  # features/feature2.feature:10
          Given Some slow step N12 # FeatureContext::someSlowStepN()
          And Some normal step N13 # FeatureContext::someNormalStepN()

        Scenario:                  # features/feature2.feature:14
          Given Some fast step N14 # FeatureContext::someFastStepN()

      @tag2
      Feature: A bit less simple feature
        In order to ...
        As a third user
        I need to ...

        Background:                # features/feature3.feature:7
          Given Some slow step N11 # FeatureContext::someSlowStepN()

        Scenario:                  # features/feature3.feature:10
          Given Some slow step N12 # FeatureContext::someSlowStepN()
          And Some normal step N13 # FeatureContext::someNormalStepN()

        Scenario:                  # features/feature3.feature:14
          Given Some fast step N14 # FeatureContext::someFastStepN()

      4 scenarios (4 passed)
      10 steps (10 passed)
      """

  Scenario: Name filters
    When I run "behat --config=behat-name-filter.php"
    Then it should pass with:
      """
      @tag1
      Feature: A simple feature
        In order to ...
        As a first user
        I need to ...

        Background:                # features/feature1.feature:7
          Given Some slow step N11 # FeatureContext::someSlowStepN()

        Scenario:                  # features/feature1.feature:10
          Given Some slow step N12 # FeatureContext::someSlowStepN()
          And Some normal step N13 # FeatureContext::someNormalStepN()

        Scenario:                  # features/feature1.feature:14
          Given Some fast step N14 # FeatureContext::someFastStepN()

      @tag2
      Feature: A bit less simple feature
        In order to ...
        As a third user
        I need to ...

        Background:                # features/feature3.feature:7
          Given Some slow step N11 # FeatureContext::someSlowStepN()

        Scenario:                  # features/feature3.feature:10
          Given Some slow step N12 # FeatureContext::someSlowStepN()
          And Some normal step N13 # FeatureContext::someNormalStepN()

        Scenario:                  # features/feature3.feature:14
          Given Some fast step N14 # FeatureContext::someFastStepN()

      @tag1 @wip
      Feature: A simple feature
        In order to ...
        As a first user
        I need to ...

        Background:                # features/wip/wip.feature:7
          Given Some slow step N11 # FeatureContext::someSlowStepN()

        Scenario:                  # features/wip/wip.feature:10
          Given Some slow step N12 # FeatureContext::someSlowStepN()
          And Some normal step N13 # FeatureContext::someNormalStepN()

        Scenario:                  # features/wip/wip.feature:14
          Given Some fast step N14 # FeatureContext::someFastStepN()

      6 scenarios (6 passed)
      15 steps (15 passed)
      """

  Scenario: Filters override
  When I run "behat --config=behat-filters-override.php -p wip features/wip/wip.feature"
  Then it should pass with:
      """
      @tag1 @wip
      Feature: A simple feature
        In order to ...
        As a first user
        I need to ...

        Background:                # features/wip/wip.feature:7
          Given Some slow step N11 # FeatureContext::someSlowStepN()

        Scenario:                  # features/wip/wip.feature:10
          Given Some slow step N12 # FeatureContext::someSlowStepN()
          And Some normal step N13 # FeatureContext::someNormalStepN()

        Scenario:                  # features/wip/wip.feature:14
          Given Some fast step N14 # FeatureContext::someFastStepN()

      2 scenarios (2 passed)
      5 steps (5 passed)
      """
