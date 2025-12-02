Feature: Legacy Snippets

  This is a deprecated usage of snippets and it is planned to go away in 4.0
  use functionality described in `snippets.feature` instead!

  Background:
    Given I initialise the working directory from the "LegacySnippets" fixtures folder
    And I provide the following options for all behat invocations:
      | option       | value    |
      | --no-colors  |          |
      | --format     | progress |

  Scenario: Regex snippets
    When I run behat with the following additional options:
      | option                  | value                    |
      | --config                | behat-regex-snippets.php |
      | features/coffee.feature |                          |
    Then it should pass with:
      """
      UUUUUUUUUUU

      2 scenarios (2 undefined)
      11 steps (11 undefined)

      --- FeatureContextCustomSnippet has missing steps. Define them with these snippets:

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

  Scenario: Regex snippets are working
    When I run behat with the following additional options:
      | option                  | value                   |
      | --config                | behat-regex-working.php |
      | --append-snippets       |                         |
      | features/coffee.feature |                         |
    And I run behat with the following additional options:
      | option                  | value                   |
      | --config                | behat-regex-working.php |
      | features/coffee.feature |                         |
    Then it should pass with:
      """
      P----P-----

      --- Pending steps:

      001 Scenario: Single quotes              # features/coffee.feature:6
            Given I have magically created 10$ # FeatureContextRegex::iHaveMagicallyCreated()
              TODO: write pending definition

      002 Scenario: Double quotes              # features/coffee.feature:18
            Given I have magically created 10$ # FeatureContextRegex::iHaveMagicallyCreated()
              TODO: write pending definition

      2 scenarios (2 pending)
      11 steps (2 pending, 9 skipped)
      """

  Scenario: Turnip snippets
    When I run behat with the following additional options:
      | option                  | value            |
      | --config                | behat-append.php |
      | features/coffee.feature |                  |
    Then it should pass with:
      """
      UUUUUUUUUUU

      2 scenarios (2 undefined)
      11 steps (11 undefined)

      --- FeatureContextSnippetAccepting has missing steps. Define them with these snippets:

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

  Scenario: Turnip snippets are working
    When I run behat with the following additional options:
      | option                  | value            |
      | --config                | behat-append.php |
      | --append-snippets       |                  |
      | features/coffee.feature |                  |
    And I run behat with the following additional options:
      | option                  | value            |
      | --config                | behat-append.php |
      | features/coffee.feature |                  |
    Then it should pass with:
      """
      P----P-----

      --- Pending steps:

      001 Scenario: Single quotes              # features/coffee.feature:6
            Given I have magically created 10$ # FeatureContextSnippetAccepting::iHaveMagicallyCreated()
              TODO: write pending definition

      002 Scenario: Double quotes              # features/coffee.feature:18
            Given I have magically created 10$ # FeatureContextSnippetAccepting::iHaveMagicallyCreated()
              TODO: write pending definition

      2 scenarios (2 pending)
      11 steps (2 pending, 9 skipped)
      """

  Scenario: Numbers with decimal points
    When I run behat with the following additional options:
      | option                   | value             |
      | --config                 | behat-decimal.php |
      | features/decimal.feature |                   |
    Then it should pass with:
      """
      UU

      1 scenario (1 undefined)
      2 steps (2 undefined)

      --- FeatureContextDecimal has missing steps. Define them with these snippets:

          #[Then(':arg1 should have value of £:arg2')]
          public function shouldHaveValueOf£($arg1, $arg2): void
          {
              throw new PendingException();
          }

      --- Don't forget these 2 use statements:

          use Behat\Behat\Tester\Exception\PendingException;
          use Behat\Step\Then;
      """
    When I run behat with the following additional options:
      | option                   | value             |
      | --config                 | behat-decimal.php |
      | --append-snippets        |                   |
      | features/decimal.feature |                   |
    And I run behat with the following additional options:
      | option                   | value             |
      | --config                 | behat-decimal.php |
      | features/decimal.feature |                   |
    Then it should pass with:
      """
      P-

      --- Pending steps:

      001 Scenario:                         # features/decimal.feature:2
            Then 5 should have value of £10 # FeatureContextDecimal::shouldHaveValueOf£()
              TODO: write pending definition

      1 scenario (1 pending)
      2 steps (1 pending, 1 skipped)
      """

  Scenario: Parameter with decimal number following string
    When I run behat with the following additional options:
      | option                   | value             |
      | --config                 | behat-package.php |
      | features/package.feature |                   |
    Then it should pass with:
      """
      U

      1 scenario (1 undefined)
      1 step (1 undefined)

      --- FeatureContextPackage has missing steps. Define them with these snippets:

          #[Given('I have a package v2.5')]
          public function iHaveAPackageV25(): void
          {
              throw new PendingException();
          }

      --- Don't forget these 2 use statements:

          use Behat\Behat\Tester\Exception\PendingException;
          use Behat\Step\Given;
      """

  Scenario: Step with slashes
    When I run behat with the following additional options:
      | option                   | value             |
      | --config                 | behat-slashes.php |
      | features/slashes.feature |                   |
    Then it should pass with:
      """
      U

      1 scenario (1 undefined)
      1 step (1 undefined)

      --- FeatureContextSlashes has missing steps. Define them with these snippets:

          #[Then('images should be uploaded to web\/uploads\/media\/default\/:arg1\/:arg2\/')]
          public function imagesShouldBeUploadedToWebUploadsMediaDefault($arg1, $arg2): void
          {
              throw new PendingException();
          }

      --- Don't forget these 2 use statements:

          use Behat\Behat\Tester\Exception\PendingException;
          use Behat\Step\Then;
      """
