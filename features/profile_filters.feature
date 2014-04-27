Feature: Filters
  In order to run only needed features
  As a Behat user
  I need to be able to use gherkin filters

  Background:
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
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
      @tag1
      Feature: A simple feature
        In order to ...
        As a first user
        I need to ...

        Background:
          Given Some slow step N11

        Scenario:
          Given Some slow step N12
          And Some normal step N13

        Scenario:
          Given Some fast step N14
      """
    And a file named "features/feature2.feature" with:
      """
      @tag2
      Feature: Second feature
        In order to ...
        As a second user
        I need to ...

        Background:
          Given Some slow step N11

        Scenario:
          Given Some slow step N12
          And Some normal step N13

        Scenario:
          Given Some fast step N14
      """
    And a file named "features/feature3.feature" with:
      """
      @tag2
      Feature: A bit less simple feature
        In order to ...
        As a third user
        I need to ...

        Background:
          Given Some slow step N11

        Scenario:
          Given Some slow step N12
          And Some normal step N13

        Scenario:
          Given Some fast step N14
      """

  Scenario: Tag filters
    Given a file named "behat.yml" with:
      """
      default:
        gherkin:
          filters:
            tags: tag2
      """
    When I run "behat --no-colors -f pretty"
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
    Given a file named "behat.yml" with:
      """
      default:
        gherkin:
          filters:
            role: second user
      """
    When I run "behat --no-colors -f pretty"
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

  Scenario: Name filters
    Given a file named "behat.yml" with:
      """
      default:
        gherkin:
          filters:
            name: simple feature
      """
    When I run "behat --no-colors -f pretty"
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

      4 scenarios (4 passed)
      10 steps (10 passed)
      """

  Scenario: Filters override
    Given a file named "features/wip.feature" with:
      """
      @tag1 @wip
      Feature: A simple feature
        In order to ...
        As a first user
        I need to ...

        Background:
          Given Some slow step N11

        Scenario:
          Given Some slow step N12
          And Some normal step N13

        Scenario:
          Given Some fast step N14
      """
    Given a file named "behat.yml" with:
      """
      default:
        gherkin:
          filters:
            tags: ~@wip

      wip:
        gherkin:
          filters:
            name: A simple feature
      """
    When I run "behat --no-colors -f pretty -p wip features/wip.feature"
    Then it should pass with:
      """
      @tag1 @wip
      Feature: A simple feature
        In order to ...
        As a first user
        I need to ...

        Background:                # features/wip.feature:7
          Given Some slow step N11 # FeatureContext::someSlowStepN()

        Scenario:                  # features/wip.feature:10
          Given Some slow step N12 # FeatureContext::someSlowStepN()
          And Some normal step N13 # FeatureContext::someNormalStepN()

        Scenario:                  # features/wip.feature:14
          Given Some fast step N14 # FeatureContext::someFastStepN()

      2 scenarios (2 passed)
      5 steps (5 passed)
      """
