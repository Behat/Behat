Feature: Different result types
  In order to differentiate feature statuses
  As a feature writer
  I need to be able to see different types of test results

  Scenario: Undefined steps
    Given a file named "features/coffee.feature" with:
      """
      Feature: Undefined coffee machine actions
        In order to make clients happy
        As a coffee machine factory
        We need to be able to tell customers
        about what coffee type is supported

        Background:
          Given I have magically created 10$

        Scenario: Buy incredible coffee
          When I have chose "coffee with turkey" in coffee machine
          Then I should have "turkey with coffee sauce"

        Scenario: Buy incredible tea
          When I have chose "pizza tea" in coffee machine
          Then I should have "pizza tea"
      """
    And a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\CustomSnippetAcceptingContext,
          Behat\Behat\Tester\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeatureContext implements CustomSnippetAcceptingContext {
          public static function getAcceptedSnippetType() { return 'regex'; }
      }
      """
    When I run "behat --no-colors -f progress features/coffee.feature"
    Then it should pass with:
      """
      UUUUUU

      2 scenarios (2 undefined)
      6 steps (6 undefined)

      --- FeatureContext has missing steps. Define them with these snippets:

          /**
           * @Given /^I have magically created (\d+)\$$/
           */
          public function iHaveMagicallyCreated($arg1)
          {
              throw new PendingException();
          }

          /**
           * @When /^I have chose "([^"]*)" in coffee machine$/
           */
          public function iHaveChoseInCoffeeMachine($arg1)
          {
              throw new PendingException();
          }

          /**
           * @Then /^I should have "([^"]*)"$/
           */
          public function iShouldHave($arg1)
          {
              throw new PendingException();
          }
      """
    When I run "behat --no-colors --strict -f progress features/coffee.feature"
    Then it should fail with:
      """
      UUUUUU

      2 scenarios (2 undefined)
      6 steps (6 undefined)

      --- FeatureContext has missing steps. Define them with these snippets:

          /**
           * @Given /^I have magically created (\d+)\$$/
           */
          public function iHaveMagicallyCreated($arg1)
          {
              throw new PendingException();
          }

          /**
           * @When /^I have chose "([^"]*)" in coffee machine$/
           */
          public function iHaveChoseInCoffeeMachine($arg1)
          {
              throw new PendingException();
          }

          /**
           * @Then /^I should have "([^"]*)"$/
           */
          public function iShouldHave($arg1)
          {
              throw new PendingException();
          }
      """

  Scenario: Pending steps
    Given a file named "features/coffee.feature" with:
      """
      Feature: Pending coffee machine actions
        In order to make some long making drinks
        As a coffee machine
        I need to be able to make pending actions

        Background:
          Given human have ordered very very very hot "coffee"

        Scenario: When the coffee ready
          When the coffee will be ready
          Then I should say "Take your cup!"
      """
    And a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\CustomSnippetAcceptingContext,
          Behat\Behat\Tester\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeatureContext implements CustomSnippetAcceptingContext
      {
          public static function getAcceptedSnippetType() { return 'regex'; }

          /**
           * @Given /^human have ordered very very very hot "([^"]*)"$/
           */
          public function humanOrdered($arg1) {
              throw new PendingException;
          }

          /**
           * @When the coffee will be ready
           */
          public function theCoffeeWillBeReady() {
              throw new PendingException;
          }
      }
      """
    When I run "behat --no-colors -f progress features/coffee.feature"
    Then it should pass with:
      """
      P-U

      --- Pending steps:

          Given human have ordered very very very hot "coffee" # FeatureContext::humanOrdered()
            TODO: write pending definition

      1 scenario (1 undefined)
      3 steps (1 undefined, 1 pending, 1 skipped)

      --- FeatureContext has missing steps. Define them with these snippets:

          /**
           * @Then /^I should say "([^"]*)"$/
           */
          public function iShouldSay($arg1)
          {
              throw new PendingException();
          }
      """
    When I run "behat --no-colors --strict -f progress features/coffee.feature"
    Then it should fail with:
      """
      P-U

      --- Pending steps:

          Given human have ordered very very very hot "coffee" # FeatureContext::humanOrdered()
            TODO: write pending definition

      1 scenario (1 undefined)
      3 steps (1 undefined, 1 pending, 1 skipped)

      --- FeatureContext has missing steps. Define them with these snippets:

          /**
           * @Then /^I should say "([^"]*)"$/
           */
          public function iShouldSay($arg1)
          {
              throw new PendingException();
          }
      """

  Scenario: Failed steps
    Given a file named "features/coffee.feature" with:
      """
      Feature: Failed coffee machine actions
        In order to know about coffee machine failures
        As a coffee buyer
        I need to be able to know about failed actions

        Background:
          Given I have thrown 10$ into machine

        Scenario: Check thrown amount
          Then I should see 12$ on the screen

        Scenario: Additional throws
          Given I have thrown 20$ into machine
          Then I should see 31$ on the screen
          And I should see 33$ on the screen
      """
    And a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context,
          Behat\Behat\Tester\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeatureContext implements Context
      {
          private $money = 0;

          /**
           * @Given /^I have thrown (\d+)\$ into machine$/
           */
          public function pay($money) {
              $this->money += $money;
          }

          /**
           * @Then /^I should see (\d+)\$ on the screen$/
           */
          public function iShouldSee($money) {
              PHPUnit_Framework_Assert::assertEquals($money, $this->money);
          }
      }
      """
    When I run "behat --no-colors -f progress features/coffee.feature"
    Then it should fail with:
      """
      .F..F-

      --- Failed steps:

          Then I should see 12$ on the screen # features/coffee.feature:10
            Failed asserting that 10 matches expected '12'.

          Then I should see 31$ on the screen # features/coffee.feature:14
            Failed asserting that 30 matches expected '31'.

      2 scenarios (2 failed)
      6 steps (3 passed, 2 failed, 1 skipped)
      """

  Scenario: Skipped steps
    Given a file named "features/coffee.feature" with:
      """
      Feature: Skipped coffee machine actions
        In order to tell clients about failures faster
        As a coffee machine
        I need to be able to skip unneeded steps

        Background:
          Given human bought coffee

        Scenario: I have no water
          Given I have no water
          And I have electricity
          When I boil water
          Then the coffee should be almost done

        Scenario: I have no electricity
          Given I have water
          And I have no electricity
          When I boil water
          Then the coffee should be almost done
      """
    And a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context,
          Behat\Behat\Tester\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeatureContext implements Context
      {
          private $money = 0;

          /** @Given /^human bought coffee$/ */
          public function humanBoughtCoffee() {}

          /** @Given /^I have water$/ */
          public function water() {}

          /** @Given /^I have no water$/ */
          public function noWater() {
              throw new Exception('NO water in coffee machine!!!');
          }

          /** @Given /^I have electricity$/ */
          public function haveElectricity() {}

          /** @Given /^I have no electricity$/ */
          public function haveNoElectricity() {
              throw new Exception('NO electricity in coffee machine!!!');
          }

          /** @When /^I boil water$/ */
          public function boilWater() {}

          /** @Then /^the coffee should be almost done$/ */
          public function coffeeAlmostDone() {}

          /**
           * @Then /^I should see (\d+)\$ on the screen$/
           */
          public function iShouldSee($money) {
              PHPUnit_Framework_Assert::assertEquals($money, $this->money);
          }
      }
      """
    When I run "behat --no-colors -f progress features/coffee.feature"
    Then it should fail with:
      """
      .F---..F--

      --- Failed steps:

          Given I have no water # features/coffee.feature:10
            NO water in coffee machine!!! (Exception)

          And I have no electricity # features/coffee.feature:17
            NO electricity in coffee machine!!! (Exception)

      2 scenarios (2 failed)
      10 steps (3 passed, 2 failed, 5 skipped)
      """

  Scenario: Ambiguous steps
    Given a file named "features/coffee.feature" with:
      """
      Feature: Ambiguous orders in coffee menu
        In order to be able to chose concrete coffee type
        As a coffee buyer
        I need to be able to know about ambiguous decisions

        Scenario: Ambiguous coffee type
          Given human have chosen "Latte"
          Then I should make him "Latte"
      """
    And a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context,
          Behat\Behat\Tester\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeatureContext implements Context
      {
          /** @Given /^human have chosen "([^"]*)"$/ */
          public function chosen($arg1) {
              throw new PendingException;
          }

          /** @Given /^human have chosen "Latte"$/ */
          public function chosenLatte() {
              throw new PendingException;
          }

          /**
           * @Then /^I should make him "([^"]*)"$/
           */
          public function iShouldSee($money) {
              throw new PendingException;
          }
      }
      """
    When I run "behat --no-colors -f progress features/coffee.feature"
    Then it should fail with:
      """
      F-

      --- Failed steps:

          Given human have chosen "Latte" # features/coffee.feature:7
            Ambiguous match of "human have chosen "Latte"":
            to `/^human have chosen "([^"]*)"$/` from FeatureContext::chosen()
            to `/^human have chosen "Latte"$/` from FeatureContext::chosenLatte()

      1 scenario (1 failed)
      2 steps (1 failed, 1 skipped)
      """

  Scenario: Redundant steps
    Given a file named "features/coffee.feature" with:
      """
      Feature: Redundant actions
        In order to be able to know about errors in definitions as soon as possible
        As a coffee machine mechanic
        I need to be able to know about redundant menu definitions

        Scenario: Redundant menu
          Given customer bought coffee
          And customer bought another one coffee
      """
    And a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context,
          Behat\Behat\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeatureContext implements Context
      {
          /** @Given /^customer bought coffee$/ */
          public function chosen($arg1) {
              // do something
          }

          /** @Given /^customer bought coffee$/ */
          public function chosenLatte() {
              // do something else
          }
      }
      """
    When I run "behat --no-colors -f progress features/coffee.feature"
    Then it should fail
    And the output should contain:
      """
      Step "/^customer bought coffee$/" is already defined in FeatureContext::chosen()
      """

  Scenario: Error-containing steps
    Given a file named "features/coffee.feature" with:
      """
      Feature: Redundant actions
        In order to be able to know about errors in definitions as soon as possible
        As a coffee machine mechanic
        I need to be able to know about redundant menu definitions

        Scenario: Redundant menu
          Given customer bought coffee
          And customer bought another one coffee
      """
    And a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context,
          Behat\Behat\Tester\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeatureContext implements Context
      {
          /** @Given /^customer bought coffee$/ */
          public function chosen() {
              trigger_error("some error", E_USER_ERROR);
          }

          /** @Given /^customer bought another one coffee$/ */
          public function chosenLatte() {
              // do something else
          }
      }
      """
    When I run "behat --no-colors -f progress features/coffee.feature"
    Then it should fail
    And the output should contain:
      """
      F-

      --- Failed steps:

          Given customer bought coffee # features/coffee.feature:7
            User Error: some error in features/bootstrap/FeatureContext.php line 12

      1 scenario (1 failed)
      2 steps (1 failed, 1 skipped)
      """
