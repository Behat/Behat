Feature: Snippets generation and addition
  In order to not manually write definitions every time
  As a feature tester
  I need tool to generate snippets for me

  Background:
    Given I initialise the working directory from the "Snippets" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value    |
      | --no-colors |          |
      | --format    | progress |

  Scenario: Generating regex snippets for particular context
    When I run "behat --snippets-for=FeatureContext --snippets-type=regex features/coffee.feature"
    Then it should pass with:
      """
      UUUUUUUUUUU

      2 scenarios (2 undefined)
      11 steps (11 undefined)

      --- FeatureContext has missing steps. Define them with these snippets:

          #[Given('/^I have magically created (\d+)\$$/')]
          public function iHaveMagicallyCreated($arg1): void
          {
              throw new PendingException();
          }

          #[When('/^I have chosen \'([^\']*)\' in coffee machine$/')]
          public function iHaveChosenInCoffeeMachine($arg1): void
          {
              throw new PendingException();
          }

          #[Then('/^I should have \'([^\']*)\'$/')]
          public function iShouldHave($arg1): void
          {
              throw new PendingException();
          }

          #[Then('/^I should get a \'([^\']*)\':$/')]
          public function iShouldGetA($arg1, PyStringNode $string): void
          {
              throw new PendingException();
          }

          #[Then('/^I should get a simple string:$/')]
          public function iShouldGetASimpleString(PyStringNode $string): void
          {
              throw new PendingException();
          }

          #[When('/^I have chosen "([^"]*)" in coffee machine$/')]
          public function iHaveChosenInCoffeeMachine2($arg1): void
          {
              throw new PendingException();
          }

          #[When('/^do something undefined with \\\\(\d+)$/')]
          public function doSomethingUndefinedWith($arg1): void
          {
              throw new PendingException();
          }

          #[Then('/^I should have "([^"]*)"$/')]
          public function iShouldHave2($arg1): void
          {
              throw new PendingException();
          }

          #[Then('/^I should get a "([^"]*)":$/')]
          public function iShouldGetA2($arg1, PyStringNode $string): void
          {
              throw new PendingException();
          }

      --- Don't forget these 5 use statements:

          use Behat\Behat\Tester\Exception\PendingException;
          use Behat\Step\Given;
          use Behat\Step\When;
          use Behat\Step\Then;
          use Behat\Gherkin\Node\PyStringNode;
      """

  Scenario: Appending regex snippets to a particular context
    When I run "behat --snippets-for=FeatureContext --snippets-type=regex --append-snippets features/coffee.feature"
    And I run "behat features/coffee.feature"
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
    And "features/bootstrap/FeatureContext.php" file should contain text:
      """
      #[Given('/^I have magically created (\d+)\$$/')]
      """
    And "features/bootstrap/FeatureContext.php" file should contain text:
      """
      use Behat\Behat\Tester\Exception\PendingException;
      """

  Scenario: Generating turnip snippets for a particular context
    When I run "behat --snippets-for=FeatureContext features/coffee.feature"
    Then it should pass with:
      """
      UUUUUUUUUUU

      2 scenarios (2 undefined)
      11 steps (11 undefined)

      --- FeatureContext has missing steps. Define them with these snippets:

          #[Given('I have magically created :arg1$')]
          public function iHaveMagicallyCreated($arg1): void
          {
              throw new PendingException();
          }

          #[When('I have chosen :arg1 in coffee machine')]
          public function iHaveChosenInCoffeeMachine($arg1): void
          {
              throw new PendingException();
          }

          #[Then('I should have :arg1')]
          public function iShouldHave($arg1): void
          {
              throw new PendingException();
          }

          #[Then('I should get a :arg1:')]
          public function iShouldGetA($arg1, PyStringNode $string): void
          {
              throw new PendingException();
          }

          #[Then('I should get a simple string:')]
          public function iShouldGetASimpleString(PyStringNode $string): void
          {
              throw new PendingException();
          }

          #[When('do something undefined with \:arg1')]
          public function doSomethingUndefinedWith($arg1): void
          {
              throw new PendingException();
          }

      --- Don't forget these 5 use statements:

          use Behat\Behat\Tester\Exception\PendingException;
          use Behat\Step\Given;
          use Behat\Step\When;
          use Behat\Step\Then;
          use Behat\Gherkin\Node\PyStringNode;
      """

  Scenario: Appending turnip snippets to a particular context
    When I run "behat --append-snippets --snippets-for=FeatureContext features/coffee.feature"
    And I run "behat features/coffee.feature"
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
    And "features/bootstrap/FeatureContext.php" file should contain text:
      """
      #[Given('I have magically created :arg1$')]
      """
    And "features/bootstrap/FeatureContext.php" file should contain text:
      """
      use Behat\Behat\Tester\Exception\PendingException;
      """

  Scenario: Generating snippets for steps that have numbers with decimal points
    When I run "behat --append-snippets --snippets-for=FeatureContext features/decimal_points.feature"
    And I run "behat --format=pretty features/decimal_points.feature"
    Then it should pass with:
      """
      Feature: Step Pattern

        Scenario:                         # features/decimal_points.feature:2
          Then 5 should have value of £10 # FeatureContext::shouldHaveValueOf£()
            TODO: write pending definition
      P    And 7 should have value of £7.2 # FeatureContext::shouldHaveValueOf£()
      -
      1 scenario (1 pending)
      2 steps (1 pending, 1 skipped)


      --- Pending steps:

      001 Scenario:                         # features/decimal_points.feature:2
            Then 5 should have value of £10 # FeatureContext::shouldHaveValueOf£()
              TODO: write pending definition

      1 scenario (1 pending)
      2 steps (1 pending, 1 skipped)
      """
    And "features/bootstrap/FeatureContext.php" file should contain text:
      """
      #[Then(':arg1 should have value of £:arg2')]
      """
    And "features/bootstrap/FeatureContext.php" file should contain text:
      """
      use Behat\Behat\Tester\Exception\PendingException;
      """

  Scenario: String followed by number with decimal point
    When I run "behat --snippets-for=FeatureContext features/package_version.feature"
    Then it should pass with:
      """
      U

      1 scenario (1 undefined)
      1 step (1 undefined)

      --- FeatureContext has missing steps. Define them with these snippets:

          #[Given('I have a package v2.5')]
          public function iHaveAPackageV25(): void
          {
              throw new PendingException();
          }

      --- Don't forget these 2 use statements:

          use Behat\Behat\Tester\Exception\PendingException;
          use Behat\Step\Given;
      """

  Scenario: Generating snippets for steps with slashes
    When I run "behat --snippets-for=FeatureContext features/upload.feature"
    Then it should pass with:
      """
      U

      1 scenario (1 undefined)
      1 step (1 undefined)

      --- FeatureContext has missing steps. Define them with these snippets:

          #[Then('images should be uploaded to web\/uploads\/media\/default\/:arg1\/:arg2\/')]
          public function imagesShouldBeUploadedToWebUploadsMediaDefault($arg1, $arg2): void
          {
              throw new PendingException();
          }

      --- Don't forget these 2 use statements:

          use Behat\Behat\Tester\Exception\PendingException;
          use Behat\Step\Then;
      """

  Scenario: Generating snippets using interactive --snippets-for
    When I answer "1" when running "behat features/coffee.feature --snippets-for"
    Then it should pass
    And the output should contain:
      """
      --- FeatureContext has missing steps. Define them with these snippets:

          #[Given('I have magically created :arg1$')]
          public function iHaveMagicallyCreated($arg1): void
          {
              throw new PendingException();
          }

          #[When('I have chosen :arg1 in coffee machine')]
          public function iHaveChosenInCoffeeMachine($arg1): void
          {
              throw new PendingException();
          }

          #[Then('I should have :arg1')]
          public function iShouldHave($arg1): void
          {
              throw new PendingException();
          }

          #[Then('I should get a :arg1:')]
          public function iShouldGetA($arg1, PyStringNode $string): void
          {
              throw new PendingException();
          }

          #[Then('I should get a simple string:')]
          public function iShouldGetASimpleString(PyStringNode $string): void
          {
              throw new PendingException();
          }

          #[When('do something undefined with \:arg1')]
          public function doSomethingUndefinedWith($arg1): void
          {
              throw new PendingException();
          }

      --- Don't forget these 5 use statements:

          use Behat\Behat\Tester\Exception\PendingException;
          use Behat\Step\Given;
          use Behat\Step\When;
          use Behat\Step\Then;
          use Behat\Gherkin\Node\PyStringNode;
      """

  Scenario: Generating snippets for steps with apostrophes
    When I run "behat --snippets-for=FeatureContext features/coffee_apostrophes.feature"
    Then it should pass with:
      """
      UUU

      1 scenario (1 undefined)
      3 steps (3 undefined)

      --- FeatureContext has missing steps. Define them with these snippets:

          #[Given('that it\'s eleven o\'clock')]
          public function thatItsElevenOclock(): void
          {
              throw new PendingException();
          }

          #[When('the guest\'s taxi has arrived')]
          public function theGuestsTaxiHasArrived(): void
          {
              throw new PendingException();
          }

          #[Then('the guest says :arg1')]
          public function theGuestSays($arg1): void
          {
              throw new PendingException();
          }

      --- Don't forget these 4 use statements:

          use Behat\Behat\Tester\Exception\PendingException;
          use Behat\Step\Given;
          use Behat\Step\When;
          use Behat\Step\Then;
      """
