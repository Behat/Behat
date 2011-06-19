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
    And a file named "features/bootstrap/FeaturesContext.php" with:
      """
      <?php

      use Behat\Behat\Context\BehatContext,
          Behat\Behat\Exception\Pending;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeaturesContext extends BehatContext
      {
          private $number;

          /**
           * @BeforeSuite
           */
          static public function doSomethingBeforeSuite($event) {
              echo "= do something before all suite run\n";
          }

          /**
           * @AfterSuite
           */
          static public function doSomethingAfterSuite($event) {
              echo "= do something after all suite run\n";
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
    When I run "behat -f pretty"
    Then it should pass with:
      """
      = do something before all suite run
      Feature:
      
        Scenario:             # features/test.feature:2
          Then I must have 50 # FeaturesContext::iMustHave()
      
        Scenario:                 # features/test.feature:4
          Given I have entered 12 # FeaturesContext::iHaveEntered()
          Then I must have 12     # FeaturesContext::iMustHave()
      
        @thirty
        Scenario:                 # features/test.feature:9
          Given I must have 30    # FeaturesContext::iMustHave()
          When I have entered 23  # FeaturesContext::iHaveEntered()
          Then I must have 23     # FeaturesContext::iMustHave()
      
        @100 @thirty
        Scenario:                 # features/test.feature:14
          Given I must have 30    # FeaturesContext::iMustHave()
          When I have entered 1   # FeaturesContext::iHaveEntered()
          Then I must have 100    # FeaturesContext::iMustHave()
      
        Scenario: 130             # features/test.feature:19
          Given I must have 130   # FeaturesContext::iMustHave()
      
      = do something after all suite run
      5 scenarios (5 passed)
      10 steps (10 passed)
      """
