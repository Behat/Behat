Feature: Snippets generation and addition
  In order to not manually write definitions every time
  As a feature tester
  I need tool to generate snippets for me

  Background:
    Given a file named "features/coffee.feature" with:
      """
      Feature: Snippets

        Background:
          Given I have magically created 10$

        Scenario: Single quotes
          When I have chose 'coffee with turkey' in coffee machine
          Then I should have 'turkey with coffee sauce'
          And I should get a 'super/string':
            '''
            Test #1
            '''
          And I should get a simple string:
            '''
            Test #2
            '''

        Scenario: Double quotes
          When I have chose "pizza tea" in coffee machine
          And do something undefined with \1
          Then I should have "pizza tea"
          And I should get a "super/string":
            '''
            Test #1
            '''
          And I should get a simple string:
            '''
            Test #2
            '''
      """

  Scenario: Generating regex snippets for particular context
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context,
          Behat\Behat\Tester\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeatureContext implements Context {}
      """
    When I run "behat --no-colors --snippets-for=FeatureContext --snippets-type=regex -f progress features/coffee.feature"
    Then it should pass with:
      """
      UUUUUUUUUUU

      2 scenarios (2 undefined)
      11 steps (11 undefined)

      --- FeatureContext has missing steps. Define them with these snippets:

          /**
           * @Given /^I have magically created (\d+)\$$/
           */
          public function iHaveMagicallyCreated($arg1)
          {
              throw new PendingException();
          }

          /**
           * @When /^I have chose '([^']*)' in coffee machine$/
           */
          public function iHaveChoseCoffeeWithTurkeyInCoffeeMachine($arg1)
          {
              throw new PendingException();
          }

          /**
           * @Then /^I should have '([^']*)'$/
           */
          public function iShouldHaveTurkeyWithCoffeeSauce($arg1)
          {
              throw new PendingException();
          }

          /**
           * @Then /^I should get a '([^']*)':$/
           */
          public function iShouldGetASuperString($arg1, PyStringNode $string)
          {
              throw new PendingException();
          }

          /**
           * @Then /^I should get a simple string:$/
           */
          public function iShouldGetASimpleString(PyStringNode $string)
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
           * @When /^do something undefined with \\(\d+)$/
           */
          public function doSomethingUndefinedWith($arg1)
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

          /**
           * @Then /^I should get a "([^"]*)":$/
           */
          public function iShouldGetA($arg1, PyStringNode $string)
          {
              throw new PendingException();
          }
      """

  Scenario: Appending regex snippets to a particular context
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context,
          Behat\Behat\Tester\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeatureContext implements Context {}
      """
    When I run "behat --no-colors -f progress --snippets-for=FeatureContext --snippets-type=regex --append-snippets features/coffee.feature"
    And I run "behat --no-colors -f progress features/coffee.feature"
    Then it should pass with:
      """
      P----P-----

      --- Pending steps:

      001 Scenario: Single quotes              # features/coffee.feature:6
            Given I have magically created 10$ # FeatureContext::iHaveMagicallyCreated()
              TODO: write pending definition

      002 Scenario: Double quotes              # features/coffee.feature:18
            Given I have magically created 10$ # FeatureContext::iHaveMagicallyCreated()
              TODO: write pending definition

      2 scenarios (2 pending)
      11 steps (2 pending, 9 skipped)
      """

  Scenario: Generating turnip snippets for a particular context
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context,
          Behat\Behat\Tester\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeatureContext implements Context {}
      """
    When I run "behat --no-colors -f progress --snippets-for=FeatureContext features/coffee.feature"
    Then it should pass with:
      """
      UUUUUUUUUUU

      2 scenarios (2 undefined)
      11 steps (11 undefined)

      --- FeatureContext has missing steps. Define them with these snippets:

          /**
           * @Given I have magically created :arg1$
           */
          public function iHaveMagicallyCreated($arg1)
          {
              throw new PendingException();
          }

          /**
           * @When I have chose :arg1 in coffee machine
           */
          public function iHaveChoseInCoffeeMachine($arg1)
          {
              throw new PendingException();
          }

          /**
           * @Then I should have :arg1
           */
          public function iShouldHave($arg1)
          {
              throw new PendingException();
          }

          /**
           * @Then I should get a :arg1:
           */
          public function iShouldGetA($arg1, PyStringNode $string)
          {
              throw new PendingException();
          }

          /**
           * @Then I should get a simple string:
           */
          public function iShouldGetASimpleString(PyStringNode $string)
          {
              throw new PendingException();
          }

          /**
           * @When do something undefined with \:arg1
           */
          public function doSomethingUndefinedWith($arg1)
          {
              throw new PendingException();
          }
      """

  Scenario: Appending turnip snippets to a particular context
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context,
          Behat\Behat\Tester\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeatureContext implements Context {}
      """
    When I run "behat --no-colors -f progress --append-snippets --snippets-for=FeatureContext features/coffee.feature"
    And I run "behat --no-colors -f progress features/coffee.feature"
    Then it should pass with:
      """
      P----P-----

      --- Pending steps:

      001 Scenario: Single quotes              # features/coffee.feature:6
            Given I have magically created 10$ # FeatureContext::iHaveMagicallyCreated()
              TODO: write pending definition

      002 Scenario: Double quotes              # features/coffee.feature:18
            Given I have magically created 10$ # FeatureContext::iHaveMagicallyCreated()
              TODO: write pending definition

      2 scenarios (2 pending)
      11 steps (2 pending, 9 skipped)
      """

  Scenario: Generating snippets for steps that have numbers with decimal points
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context {}
      """
    And a file named "features/coffee.feature" with:
      """
      Feature: Step Pattern
        Scenario:
          Then 5 should have value of £10
          And 7 should have value of £7.2
      """
    When I run "behat -f progress --no-colors --append-snippets --snippets-for=FeatureContext"
    And I run "behat -f pretty --no-colors"
    Then it should pass with:
      """
      Feature: Step Pattern

        Scenario:                         # features/coffee.feature:2
          Then 5 should have value of £10 # FeatureContext::shouldHaveValueOfPs()
            TODO: write pending definition
          And 7 should have value of £7.2 # FeatureContext::shouldHaveValueOfPs()

      1 scenario (1 pending)
      2 steps (1 pending, 1 skipped)
      """

  Scenario: Generating snippets for steps that have string followed by decimal number
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context {}
      """
    And a file named "features/coffee.feature" with:
      """
      Feature: Step Pattern
        Scenario:
          Given I have a package v2.5
      """
    When I run "behat -f progress --no-colors --snippets-for=FeatureContext"
    Then it should pass with:
      """
      U

      1 scenario (1 undefined)
      1 step (1 undefined)

      --- FeatureContext has missing steps. Define them with these snippets:

          /**
           * @Given I have a package v2.5
           */
          public function iHaveAPackageV()
          {
              throw new PendingException();
          }
      """

  Scenario: Generating snippets for steps with slashes
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context {}
      """
    And a file named "features/coffee.feature" with:
      """
      Feature: Step Pattern
        Scenario:
          Then images should be uploaded to web/uploads/media/default/0001/01/
      """
    When I run "behat -f progress --no-colors --snippets-for=FeatureContext"
    Then it should pass with:
      """
      U

      1 scenario (1 undefined)
      1 step (1 undefined)

      --- FeatureContext has missing steps. Define them with these snippets:

          /**
           * @Then images should be uploaded to web\/uploads\/media\/default\/:arg1\/:arg2\/
           */
          public function imagesShouldBeUploadedToWebUploadsMediaDefault($arg1, $arg2)
          {
              throw new PendingException();
          }
      """

  Scenario: Generating snippets using interactive --snippets-for
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context,
          Behat\Behat\Tester\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeatureContext implements Context {}
      """
    When I answer "1" when running "behat --no-colors -f progress --snippets-for"
    Then it should pass
    And the output should contain:
      """
      --- FeatureContext has missing steps. Define them with these snippets:

          /**
           * @Given I have magically created :arg1$
           */
          public function iHaveMagicallyCreated($arg1)
          {
              throw new PendingException();
          }

          /**
           * @When I have chose :arg1 in coffee machine
           */
          public function iHaveChoseInCoffeeMachine($arg1)
          {
              throw new PendingException();
          }

          /**
           * @Then I should have :arg1
           */
          public function iShouldHave($arg1)
          {
              throw new PendingException();
          }

          /**
           * @Then I should get a :arg1:
           */
          public function iShouldGetA($arg1, PyStringNode $string)
          {
              throw new PendingException();
          }

          /**
           * @Then I should get a simple string:
           */
          public function iShouldGetASimpleString(PyStringNode $string)
          {
              throw new PendingException();
          }

          /**
           * @When do something undefined with \:arg1
           */
          public function doSomethingUndefinedWith($arg1)
          {
              throw new PendingException();
          }
      """

  Scenario: Generating snippets for steps with apostrophes
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context {}
      """
    And a file named "features/coffee.feature" with:
      """
      Feature: Step Pattern
        Scenario:
          Given that it's eleven o'clock
          When the guest's taxi has arrived
          Then the guest says 'Goodbye'
      """
    When I run "behat -f progress --no-colors --snippets-for=FeatureContext"
    Then it should pass with:
      """
      UUU

      1 scenario (1 undefined)
      3 steps (3 undefined)

      --- FeatureContext has missing steps. Define them with these snippets:

          /**
           * @Given that it's eleven o'clock
           */
          public function thatItsElevenOclock()
          {
              throw new PendingException();
          }

          /**
           * @When the guest's taxi has arrived
           */
          public function theGuestsTaxiHasArrived()
          {
              throw new PendingException();
          }

          /**
           * @Then the guest says :arg1
           */
          public function theGuestSays($arg1)
          {
              throw new PendingException();
          }
      """
