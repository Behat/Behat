Feature: Append snippets option
  In order to use definition snippets fully
  As a context developer
  I need to be able to autoappend snippets to context

  Background:
    Given I initialise the working directory from the "AppendSnippets" fixtures folder

  Scenario: Append snippets to main context
    When I run "behat -f progress --append-snippets --snippets-for=FeatureContext --snippets-type=regex"
    Then "features/bootstrap/FeatureContext.php" file should contain:
      """
      <?php

      use Behat\Behat\Context\Context;
      use Behat\Behat\Tester\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode;
      use Behat\Gherkin\Node\TableNode;
      use Behat\Step\Given;
      use Behat\Step\Then;
      use Behat\Step\When;

      class FeatureContext implements Context
      {
          private $apples = 0;
          private $parameters;

          public function __construct(array $parameters = [])
          {
              $this->parameters = $parameters;
          }

          #[Given('/^I have (\\d+) apples?$/')]
          public function iHaveApples($count)
          {
              $this->apples = intval($count);
          }

          #[When('/^I ate (\\d+) apples?$/')]
          public function iAteApples($count)
          {
              $this->apples -= intval($count);
          }

          #[When('/^I found (\\d+) apples?$/')]
          public function iFoundApples($count)
          {
              $this->apples += intval($count);
          }

          #[Then('/^I should have (\\d+) apples$/')]
          public function iShouldHaveApples($count)
          {
              PHPUnit\Framework\Assert::assertEquals(intval($count), $this->apples);
          }

          #[Then('/^context parameter "([^"]*)" should be equal to "([^"]*)"$/')]
          public function contextParameterShouldBeEqualTo($key, $val)
          {
              PHPUnit\Framework\Assert::assertEquals($val, $this->parameters[$key]);
          }

          #[Given('/^context parameter "([^"]*)" should be array with (\\d+) elements$/')]
          public function contextParameterShouldBeArrayWithElements($key, $count)
          {
              PHPUnit\Framework\Assert::assertIsArray($this->parameters[$key]);
              PHPUnit\Framework\Assert::assertEquals(2, count($this->parameters[$key]));
          }

          private function doSomethingUndefinedWith()
          {
          }

          /**
           * This dummy method is added just so that PHP-CS-Fixer does not
           * complain about unused import `use` statements.
           */
          private function useClasses(PyStringNode $node, TableNode $table)
          {
              throw new PendingException();
          }

          #[Then('/^do something undefined with \$$/')]
          public function doSomethingUndefinedWith2(): void
          {
              throw new PendingException();
          }

          #[Then('/^do something undefined with \\\\(\d+)$/')]
          public function doSomethingUndefinedWith3($arg1): void
          {
              throw new PendingException();
          }

          #[Given('/^pystring:$/')]
          public function pystring(PyStringNode $string): void
          {
              throw new PendingException();
          }

          #[Given('/^pystring (\d+):$/')]
          public function pystring2($arg1, PyStringNode $string): void
          {
              throw new PendingException();
          }

          #[Given('/^table:$/')]
          public function table(TableNode $table): void
          {
              throw new PendingException();
          }
      }
      """

  Scenario: Append snippets to main context with auto use PendingException
    When I run "behat -f progress --append-snippets --snippets-for=FeatureContextNoPendingException --snippets-type=regex --profile=no_pending_exception"
    Then "features/bootstrap/FeatureContextNoPendingException.php" file should contain:
      """
      <?php

      use Behat\Behat\Tester\Exception\PendingException;
      use Behat\Behat\Context\Context;
      use Behat\Gherkin\Node\PyStringNode;
      use Behat\Gherkin\Node\TableNode;
      use Behat\Step\Given;
      use Behat\Step\Then;
      use Behat\Step\When;

      class FeatureContextNoPendingException implements Context
      {
          private $apples = 0;
          private $parameters;

          public function __construct(array $parameters = [])
          {
              $this->parameters = $parameters;
          }

          #[Given('/^I have (\\d+) apples?$/')]
          public function iHaveApples($count)
          {
              $this->apples = intval($count);
          }

          #[When('/^I ate (\\d+) apples?$/')]
          public function iAteApples($count)
          {
              $this->apples -= intval($count);
          }

          #[When('/^I found (\\d+) apples?$/')]
          public function iFoundApples($count)
          {
              $this->apples += intval($count);
          }

          #[Then('/^I should have (\\d+) apples$/')]
          public function iShouldHaveApples($count)
          {
              PHPUnit\Framework\Assert::assertEquals(intval($count), $this->apples);
          }

          #[Then('/^context parameter "([^"]*)" should be equal to "([^"]*)"$/')]
          public function contextParameterShouldBeEqualTo($key, $val)
          {
              PHPUnit\Framework\Assert::assertEquals($val, $this->parameters[$key]);
          }

          #[Given('/^context parameter "([^"]*)" should be array with (\\d+) elements$/')]
          public function contextParameterShouldBeArrayWithElements($key, $count)
          {
              PHPUnit\Framework\Assert::assertIsArray($this->parameters[$key]);
              PHPUnit\Framework\Assert::assertEquals(2, count($this->parameters[$key]));
          }

          private function doSomethingUndefinedWith()
          {
          }

          /**
           * This dummy method is added just so that PHP-CS-Fixer does not
           * complain about unused import `use` statements.
           */
          private function useClasses(PyStringNode $node, TableNode $table)
          {
          }

          #[Then('/^do something undefined with \$$/')]
          public function doSomethingUndefinedWith2(): void
          {
              throw new PendingException();
          }

          #[Then('/^do something undefined with \\\\(\d+)$/')]
          public function doSomethingUndefinedWith3($arg1): void
          {
              throw new PendingException();
          }

          #[Given('/^pystring:$/')]
          public function pystring(PyStringNode $string): void
          {
              throw new PendingException();
          }

          #[Given('/^pystring (\d+):$/')]
          public function pystring2($arg1, PyStringNode $string): void
          {
              throw new PendingException();
          }

          #[Given('/^table:$/')]
          public function table(TableNode $table): void
          {
              throw new PendingException();
          }
      }
      """

  Scenario: Append snippets to main context with auto use PendingException
    When I run "behat -f progress --append-snippets --snippets-for=FeatureContextMinimalImports --snippets-type=regex --profile=minimal_imports"
    Then "features/bootstrap/FeatureContextMinimalImports.php" file should contain:
      """
      <?php

      use Behat\Gherkin\Node\TableNode;
      use Behat\Gherkin\Node\PyStringNode;
      use Behat\Behat\Tester\Exception\PendingException;
      use Behat\Behat\Context\Context;
      use Behat\Step\Given;
      use Behat\Step\Then;
      use Behat\Step\When;

      class FeatureContextMinimalImports implements Context
      {
          private $apples = 0;
          private $parameters;

          public function __construct(array $parameters = [])
          {
              $this->parameters = $parameters;
          }

          #[Given('/^I have (\\d+) apples?$/')]
          public function iHaveApples($count)
          {
              $this->apples = intval($count);
          }

          #[When('/^I ate (\\d+) apples?$/')]
          public function iAteApples($count)
          {
              $this->apples -= intval($count);
          }

          #[When('/^I found (\\d+) apples?$/')]
          public function iFoundApples($count)
          {
              $this->apples += intval($count);
          }

          #[Then('/^I should have (\\d+) apples$/')]
          public function iShouldHaveApples($count)
          {
              PHPUnit\Framework\Assert::assertEquals(intval($count), $this->apples);
          }

          #[Then('/^context parameter "([^"]*)" should be equal to "([^"]*)"$/')]
          public function contextParameterShouldBeEqualTo($key, $val)
          {
              PHPUnit\Framework\Assert::assertEquals($val, $this->parameters[$key]);
          }

          #[Given('/^context parameter "([^"]*)" should be array with (\\d+) elements$/')]
          public function contextParameterShouldBeArrayWithElements($key, $count)
          {
              PHPUnit\Framework\Assert::assertIsArray($this->parameters[$key]);
              PHPUnit\Framework\Assert::assertEquals(2, count($this->parameters[$key]));
          }

          private function doSomethingUndefinedWith()
          {
          }

          #[Then('/^do something undefined with \$$/')]
          public function doSomethingUndefinedWith2(): void
          {
              throw new PendingException();
          }

          #[Then('/^do something undefined with \\\\(\d+)$/')]
          public function doSomethingUndefinedWith3($arg1): void
          {
              throw new PendingException();
          }

          #[Given('/^pystring:$/')]
          public function pystring(PyStringNode $string): void
          {
              throw new PendingException();
          }

          #[Given('/^pystring (\d+):$/')]
          public function pystring2($arg1, PyStringNode $string): void
          {
              throw new PendingException();
          }

          #[Given('/^table:$/')]
          public function table(TableNode $table): void
          {
              throw new PendingException();
          }
      }
      """

  Scenario: Append snippets to accepting context only
    When I run "behat -f progress --append-snippets --snippets-for=FirstContext --snippets-type=regex --no-colors --profile=multicontext"
    Then it should pass with:
      """
      UUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUU

      14 scenarios (14 undefined)
      58 steps (58 undefined)

      --- Use --snippets-for CLI option to generate snippets for following second suite steps:

          Given I have 3 apples
          When I ate 1 apple
          Then I should have 3 apples
          When I found 5 apples
          Then I should have 8 apples
          And I found 2 apples
          Then I should have 5 apples
          And do something undefined with $
          When I ate 3 apples
          And I found 1 apples
          Then I should have 1 apples
          And do something undefined with \1
          When I ate 0 apples
          And I found 4 apples
          When I ate 2 apples
          Given pystring:
          And pystring 5:
          And table:


      u features/bootstrap/FirstContext.php - `I have 3 apples` definition added
      u features/bootstrap/FirstContext.php - `I ate 1 apple` definition added
      u features/bootstrap/FirstContext.php - `I should have 3 apples` definition added
      u features/bootstrap/FirstContext.php - `I found 5 apples` definition added
      u features/bootstrap/FirstContext.php - `do something undefined with $` definition added
      u features/bootstrap/FirstContext.php - `I ate 3 apples` definition added
      u features/bootstrap/FirstContext.php - `do something undefined with \1` definition added
      u features/bootstrap/FirstContext.php - `pystring:` definition added
      u features/bootstrap/FirstContext.php - `pystring 5:` definition added
      u features/bootstrap/FirstContext.php - `table:` definition added
      """
    And "features/bootstrap/FirstContext.php" file should contain:
      """
      <?php

      use Behat\Gherkin\Node\TableNode;
      use Behat\Gherkin\Node\PyStringNode;
      use Behat\Step\Then;
      use Behat\Step\When;
      use Behat\Step\Given;
      use Behat\Behat\Tester\Exception\PendingException;
      use Behat\Behat\Context\Context;

      class FirstContext implements Context
      {

          #[Given('/^I have (\d+) apples$/')]
          public function iHaveApples($arg1): void
          {
              throw new PendingException();
          }

          #[When('/^I ate (\d+) apple$/')]
          public function iAteApple($arg1): void
          {
              throw new PendingException();
          }

          #[Then('/^I should have (\d+) apples$/')]
          public function iShouldHaveApples($arg1): void
          {
              throw new PendingException();
          }

          #[When('/^I found (\d+) apples$/')]
          public function iFoundApples($arg1): void
          {
              throw new PendingException();
          }

          #[Then('/^do something undefined with \$$/')]
          public function doSomethingUndefinedWith(): void
          {
              throw new PendingException();
          }

          #[When('/^I ate (\d+) apples$/')]
          public function iAteApples($arg1): void
          {
              throw new PendingException();
          }

          #[Then('/^do something undefined with \\\\(\d+)$/')]
          public function doSomethingUndefinedWith2($arg1): void
          {
              throw new PendingException();
          }

          #[Given('/^pystring:$/')]
          public function pystring(PyStringNode $string): void
          {
              throw new PendingException();
          }

          #[Given('/^pystring (\d+):$/')]
          public function pystring2($arg1, PyStringNode $string): void
          {
              throw new PendingException();
          }

          #[Given('/^table:$/')]
          public function table(TableNode $table): void
          {
              throw new PendingException();
          }
      }
      """
    And "features/bootstrap/SecondContext.php" file should contain:
      """
      <?php

      use Behat\Behat\Context\Context;

      class SecondContext implements Context
      {
      }
      """
