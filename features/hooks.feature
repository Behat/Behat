Feature: hooks
  In order to hook into Behat testing process
  As a tester
  I need to be able to write hooks

  Background:
    Given a file named "behat.yml" with:
      """
      default:
        suites:
          default:
            parameters:
              before_feature: BEFORE EVERY FEATURE
              after_feature:  AFTER EVERY FEATURE
      """
    And a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeatureContext implements Context
      {
          private $number;

          /**
           * @BeforeFeature
           */
          static public function doSomethingBeforeFeature($event) {
              $params = $event->getSuite()->getSetting('parameters');
              echo "= do something ".$params['before_feature'];
          }

          /**
           * @AfterFeature
           */
          static public function doSomethingAfterFeature($event) {
              $params = $event->getSuite()->getSetting('parameters');
              echo "= do something ".$params['after_feature'];
          }

          /**
           * @BeforeFeature @someFeature
           */
          static public function doSomethingBeforeSomeFeature($event) {
              echo "= do something before SOME feature";
          }

          /**
           * @AfterFeature @someFeature
           */
          static public function doSomethingAfterSomeFeature($event) {
              echo "= do something after SOME feature";
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
           * @BeforeScenario @exception
           */
          public function beforeScenarioException($event) {
              throw new \Exception('Exception');
          }

          /**
           * @BeforeStep I must have 100
           */
          public function beforeStep100($event) {
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
              \PHPUnit_Framework_Assert::assertEquals(intval($number), $this->number);
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
    When I run "behat --no-colors -f pretty"
    Then it should pass with:
      """
      ┌─ @BeforeFeature # FeatureContext::doSomethingBeforeFeature()
      │
      │  = do something BEFORE EVERY FEATURE
      │
      Feature:

        Scenario:             # features/test.feature:2
          Then I must have 50 # FeatureContext::iMustHave()

        Scenario:                 # features/test.feature:4
          Given I have entered 12 # FeatureContext::iHaveEntered()
          Then I must have 12     # FeatureContext::iMustHave()

        @thirty
        Scenario:                # features/test.feature:9
          Given I must have 30   # FeatureContext::iMustHave()
          When I have entered 23 # FeatureContext::iHaveEntered()
          Then I must have 23    # FeatureContext::iMustHave()

        @100 @thirty
        Scenario:               # features/test.feature:14
          Given I must have 30  # FeatureContext::iMustHave()
          When I have entered 1 # FeatureContext::iHaveEntered()
          Then I must have 100  # FeatureContext::iMustHave()

        Scenario: 130           # features/test.feature:19
          Given I must have 130 # FeatureContext::iMustHave()

      │
      │  = do something AFTER EVERY FEATURE
      │
      └─ @AfterFeature # FeatureContext::doSomethingAfterFeature()

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
    When I run "behat --no-colors -f pretty"
    Then it should pass with:
      """
      ┌─ @BeforeFeature # FeatureContext::doSomethingBeforeFeature()
      │
      │  = do something BEFORE EVERY FEATURE
      │
      Feature:

        Scenario:             # features/1-one.feature:2
          Then I must have 50 # FeatureContext::iMustHave()

        Scenario:                 # features/1-one.feature:5
          Given I have entered 12 # FeatureContext::iHaveEntered()
          Then I must have 12     # FeatureContext::iMustHave()

        Scenario: 130           # features/1-one.feature:9
          Given I must have 130 # FeatureContext::iMustHave()

      │
      │  = do something AFTER EVERY FEATURE
      │
      └─ @AfterFeature # FeatureContext::doSomethingAfterFeature()

      ┌─ @BeforeFeature # FeatureContext::doSomethingBeforeFeature()
      │
      │  = do something BEFORE EVERY FEATURE
      │
      ┌─ @BeforeFeature @someFeature # FeatureContext::doSomethingBeforeSomeFeature()
      │
      │  = do something before SOME feature
      │
      @someFeature
      Feature:

        Scenario: 130           # features/2-two.feature:3
          Given I must have 130 # FeatureContext::iMustHave()

      │
      │  = do something AFTER EVERY FEATURE
      │
      └─ @AfterFeature # FeatureContext::doSomethingAfterFeature()

      │
      │  = do something after SOME feature
      │
      └─ @AfterFeature @someFeature # FeatureContext::doSomethingAfterSomeFeature()

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
    When I run "behat --no-colors -f pretty"
    Then it should pass with:
      """
      ┌─ @BeforeFeature # FeatureContext::doSomethingBeforeFeature()
      │
      │  = do something BEFORE EVERY FEATURE
      │
      Feature:

        Background:            # features/background.feature:2
          Given I must have 50 # FeatureContext::iMustHave()

        Scenario:                 # features/background.feature:5
          Given I have entered 12 # FeatureContext::iHaveEntered()
          Then I must have 12     # FeatureContext::iMustHave()

      │
      │  = do something AFTER EVERY FEATURE
      │
      └─ @AfterFeature # FeatureContext::doSomethingAfterFeature()

      1 scenario (1 passed)
      3 steps (3 passed)
      """

  Scenario: Background exceptions
    Given a file named "features/background.feature" with:
    """
      Feature:

        @exception
        Scenario:
          Then I must have 50
        Scenario:
          Given I have entered 12
          Then I must have 12

        @exception
        Scenario:
          Given I must have 30
          When I have entered 23
          Then I must have 23

        Scenario: 130
          Given I must have 130
      """
    When I run "behat --no-colors -f pretty"
    Then it should fail with:
      """
      ┌─ @BeforeFeature # FeatureContext::doSomethingBeforeFeature()
      │
      │  = do something BEFORE EVERY FEATURE
      │
      Feature:

        ┌─ @BeforeScenario @exception # FeatureContext::beforeScenarioException()
        │
        ╳  Exception (Exception)
        │
        @exception
        Scenario:             # features/background.feature:4
          Then I must have 50 # FeatureContext::iMustHave()

        Scenario:                 # features/background.feature:6
          Given I have entered 12 # FeatureContext::iHaveEntered()
          Then I must have 12     # FeatureContext::iMustHave()

        ┌─ @BeforeScenario @exception # FeatureContext::beforeScenarioException()
        │
        ╳  Exception (Exception)
        │
        @exception
        Scenario:                # features/background.feature:11
          Given I must have 30   # FeatureContext::iMustHave()
          When I have entered 23 # FeatureContext::iHaveEntered()
          Then I must have 23    # FeatureContext::iMustHave()

        Scenario: 130           # features/background.feature:16
          Given I must have 130 # FeatureContext::iMustHave()

      │
      │  = do something AFTER EVERY FEATURE
      │
      └─ @AfterFeature # FeatureContext::doSomethingAfterFeature()

      --- Skipped scenarios:

          features/background.feature:4
          features/background.feature:11

      4 scenarios (2 passed, 2 skipped)
      7 steps (3 passed, 4 skipped)
      """

  Scenario: Step state doesn't affect after hooks
    Given a file named "features/test.feature" with:
      """
      Feature:

        Scenario:
          Given passing step

        Scenario:
          Given failing step

        Scenario:
          Given passing step with failing hook

        @failing-before-hook
        Scenario:
          Given passing step
      """
    And a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          /** @BeforeScenario */
          public function passingBeforeScenarioHook()
          {
              echo 'Is passing';
          }

          /** @BeforeScenario @failing-before-hook */
          public function failingBeforeScenarioHook()
          {
              throw new \RuntimeException('Is failing');
          }

          /** @AfterScenario */
          public function passingAfterScenarioHook()
          {
              echo 'Is passing';
          }

          /** @BeforeStep */
          public function passingBeforeStep()
          {
              echo 'Is passing';
          }

          /** @BeforeStep passing step with failing hook */
          public function failingBeforeStep()
          {
              throw new \RuntimeException('Is failing');
          }

          /** @AfterStep */
          public function passingAfterStep()
          {
              echo 'Is passing';
          }

          /**
           * @Given passing step
           * @Given passing step with failing hook
           */
          public function passingStep()
          {
              echo 'Is passing';
          }

          /** @Given failing step */
          public function failingStep()
          {
              throw new \RuntimeException('Failing');
          }
      }
      """
    When I run "behat --no-colors -f pretty"
    Then it should fail with:
      """
      Feature:

        ┌─ @BeforeScenario # FeatureContext::passingBeforeScenarioHook()
        │
        │  Is passing
        │
        Scenario:            # features/test.feature:3
          ┌─ @BeforeStep # FeatureContext::passingBeforeStep()
          │
          │  Is passing
          │
          Given passing step # FeatureContext::passingStep()
            │ Is passing
          │
          │  Is passing
          │
          └─ @AfterStep # FeatureContext::passingAfterStep()
        │
        │  Is passing
        │
        └─ @AfterScenario # FeatureContext::passingAfterScenarioHook()

        ┌─ @BeforeScenario # FeatureContext::passingBeforeScenarioHook()
        │
        │  Is passing
        │
        Scenario:            # features/test.feature:6
          ┌─ @BeforeStep # FeatureContext::passingBeforeStep()
          │
          │  Is passing
          │
          Given failing step # FeatureContext::failingStep()
            Failing (RuntimeException)
          │
          │  Is passing
          │
          └─ @AfterStep # FeatureContext::passingAfterStep()
        │
        │  Is passing
        │
        └─ @AfterScenario # FeatureContext::passingAfterScenarioHook()

        ┌─ @BeforeScenario # FeatureContext::passingBeforeScenarioHook()
        │
        │  Is passing
        │
        Scenario:                              # features/test.feature:9
          ┌─ @BeforeStep # FeatureContext::passingBeforeStep()
          │
          │  Is passing
          │
          ┌─ @BeforeStep passing step with failing hook # FeatureContext::failingBeforeStep()
          │
          ╳  Is failing (RuntimeException)
          │
          Given passing step with failing hook # FeatureContext::passingStep()
        │
        │  Is passing
        │
        └─ @AfterScenario # FeatureContext::passingAfterScenarioHook()

        ┌─ @BeforeScenario # FeatureContext::passingBeforeScenarioHook()
        │
        │  Is passing
        │
        ┌─ @BeforeScenario @failing-before-hook # FeatureContext::failingBeforeScenarioHook()
        │
        ╳  Is failing (RuntimeException)
        │
        @failing-before-hook
        Scenario:            # features/test.feature:13
          Given passing step # FeatureContext::passingStep()

      --- Skipped scenarios:

          features/test.feature:13

      --- Failed scenarios:

          features/test.feature:6
          features/test.feature:9

      4 scenarios (1 passed, 2 failed, 1 skipped)
      4 steps (1 passed, 1 failed, 2 skipped)
      """
