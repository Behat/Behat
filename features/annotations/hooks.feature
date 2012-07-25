Feature: hooks
  In order to hook into Behat testing process
  As a tester
  I need to be able to write hooks

  Background:
    Given a file named "features/bootstrap/bootstrap.php" with:
      """
      <?php
      require_once 'PHPUnit/Autoload.php';
      require_once 'PHPUnit/Framework/Assert/Functions.php';
      """
    And a file named "behat.yml" with:
      """
      default:
        context:
          parameters:
            before_suite:   BEFORE ANY SUITE
            after_suite:    AFTER ANY SUITE
            before_feature: BEFORE EVERY FEATURE
            after_feature:  AFTER EVERY FEATURE
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
          private $number;

          /**
           * @BeforeSuite
           */
          static public function doSomethingBeforeSuite($event) {
              $params = $event->getContextParameters();
              echo "= do something ".$params['before_suite']."\n";
          }

          /**
           * @BeforeFeature
           */
          static public function doSomethingBeforeFeature($event) {
              $params = $event->getContextParameters();
              echo "= do something ".$params['before_feature']."\n";
          }

          /**
           * @AfterFeature
           */
          static public function doSomethingAfterFeature($event) {
              $params = $event->getContextParameters();
              echo "= do something ".$params['after_feature']."\n";
          }

          /**
           * @BeforeFeature @someFeature
           */
          static public function doSomethingBeforeSomeFeature($event) {
              echo "= do something before SOME feature\n";
          }

          /**
           * @AfterFeature @someFeature
           */
          static public function doSomethingAfterSomeFeature($event) {
              echo "= do something after SOME feature\n";
          }

          /**
           * @AfterSuite
           */
          static public function doSomethingAfterSuite($event) {
              $params = $event->getContextParameters();
              echo "= do something ".$params['after_suite']."\n";
          }

          /**
           * @BeforeScenario
           */
          public function beforeScenario($event) {
              $this->number = 50;
          }

          /**
           * @BeforeScenario 130
           */
          public function beforeScenario130($event) {
              $this->number = 130;
          }

          /**
           * @BeforeScenario @thirty
           */
          public function beforeScenarioThirty($event) {
              $this->number = 30;
          }

          /**
           * @AfterStep @100
           */
          public function afterStep100($event) {
              $this->number = 100;
          }

          /**
           * @Given /^I have entered (\d+)$/
           */
          public function iHaveEntered($number) {
              $this->number = intval($number);
          }

          /**
           * @Then /^I must have (\d+)$/
           */
          public function iMustHave($number) {
              assertEquals(intval($number), $this->number);
          }
      }
      """

  Scenario:
    Given a file named "features/test.feature" with:
      """
      Feature:
        Scenario:
          Then I must have 50
        Scenario:
          Given I have entered 12
          Then I must have 12

        @thirty
        Scenario:
          Given I must have 30
          When I have entered 23
          Then I must have 23
        @100 @thirty
        Scenario:
          Given I must have 30
          When I have entered 1
          Then I must have 100

        Scenario: 130
          Given I must have 130
      """
    When I run "behat --no-ansi -f pretty"
    Then it should pass with:
      """
      = do something BEFORE ANY SUITE
      = do something BEFORE EVERY FEATURE
      Feature:

        Scenario:             # features/test.feature:2
          Then I must have 50 # FeatureContext::iMustHave()

        Scenario:                 # features/test.feature:4
          Given I have entered 12 # FeatureContext::iHaveEntered()
          Then I must have 12     # FeatureContext::iMustHave()

        @thirty
        Scenario:                 # features/test.feature:9
          Given I must have 30    # FeatureContext::iMustHave()
          When I have entered 23  # FeatureContext::iHaveEntered()
          Then I must have 23     # FeatureContext::iMustHave()

        @100 @thirty
        Scenario:                 # features/test.feature:14
          Given I must have 30    # FeatureContext::iMustHave()
          When I have entered 1   # FeatureContext::iHaveEntered()
          Then I must have 100    # FeatureContext::iMustHave()

        Scenario: 130             # features/test.feature:19
          Given I must have 130   # FeatureContext::iMustHave()

      = do something AFTER EVERY FEATURE
      = do something AFTER ANY SUITE
      5 scenarios (5 passed)
      10 steps (10 passed)
      """

  Scenario: Filter features
    Given a file named "features/1-one.feature" with:
      """
      Feature:
        Scenario:
          Then I must have 50

        Scenario:
          Given I have entered 12
          Then I must have 12

        Scenario: 130
          Given I must have 130
      """
    Given a file named "features/2-two.feature" with:
      """
      @someFeature
      Feature:
        Scenario: 130
          Given I must have 130
      """
    When I run "behat --no-ansi -f pretty"
    Then it should pass with:
      """
      = do something BEFORE ANY SUITE
      = do something BEFORE EVERY FEATURE
      Feature:

        Scenario:             # features/1-one.feature:2
          Then I must have 50 # FeatureContext::iMustHave()

        Scenario:                 # features/1-one.feature:5
          Given I have entered 12 # FeatureContext::iHaveEntered()
          Then I must have 12     # FeatureContext::iMustHave()

        Scenario: 130             # features/1-one.feature:9
          Given I must have 130   # FeatureContext::iMustHave()

      = do something AFTER EVERY FEATURE
      = do something BEFORE EVERY FEATURE
      = do something before SOME feature
      @someFeature
      Feature:

        Scenario: 130             # features/2-two.feature:3
          Given I must have 130   # FeatureContext::iMustHave()

      = do something AFTER EVERY FEATURE
      = do something after SOME feature
      = do something AFTER ANY SUITE
      4 scenarios (4 passed)
      5 steps (5 passed)
      """

  Scenario: Background step hooks
    Given a file named "features/background.feature" with:
      """
      Feature:
        Background:
          Given I must have 50

        Scenario:
          Given I have entered 12
          Then I must have 12
      """
    When I run "behat --no-ansi -f pretty"
    Then it should pass with:
      """
      = do something BEFORE ANY SUITE
      = do something BEFORE EVERY FEATURE
      Feature:

        Background:            # features/background.feature:2
          Given I must have 50 # FeatureContext::iMustHave()

        Scenario:                 # features/background.feature:5
          Given I have entered 12 # FeatureContext::iHaveEntered()
          Then I must have 12     # FeatureContext::iMustHave()

      = do something AFTER EVERY FEATURE
      = do something AFTER ANY SUITE
      1 scenario (1 passed)
      3 steps (3 passed)
      """
