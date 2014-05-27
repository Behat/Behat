Feature: Append snippets option
  In order to use definition snippets fully
  As a context developer
  I need to be able to autoappend snippets to context

  Background:
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\CustomSnippetAcceptingContext,
          Behat\Behat\Tester\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeatureContext implements CustomSnippetAcceptingContext
      {
          private $apples = 0;
          private $parameters;

          public static function getAcceptedSnippetType() { return 'regex'; }

          public function __construct(array $parameters = array()) {
              $this->parameters = $parameters;
          }

          /**
           * @Given /^I have (\d+) apples?$/
           */
          public function iHaveApples($count) {
              $this->apples = intval($count);
          }

          /**
           * @When /^I ate (\d+) apples?$/
           */
          public function iAteApples($count) {
              $this->apples -= intval($count);
          }

          /**
           * @When /^I found (\d+) apples?$/
           */
          public function iFoundApples($count) {
              $this->apples += intval($count);
          }

          /**
           * @Then /^I should have (\d+) apples$/
           */
          public function iShouldHaveApples($count) {
              \PHPUnit_Framework_Assert::assertEquals(intval($count), $this->apples);
          }

          /**
           * @Then /^context parameter "([^"]*)" should be equal to "([^"]*)"$/
           */
          public function contextParameterShouldBeEqualTo($key, $val) {
              \PHPUnit_Framework_Assert::assertEquals($val, $this->parameters[$key]);
          }

          /**
           * @Given /^context parameter "([^"]*)" should be array with (\d+) elements$/
           */
          public function contextParameterShouldBeArrayWithElements($key, $count) {
              \PHPUnit_Framework_Assert::assertInternalType('array', $this->parameters[$key]);
              \PHPUnit_Framework_Assert::assertEquals(2, count($this->parameters[$key]));
          }

          private function doSomethingUndefinedWith() {}
      }
      """
    And a file named "features/apples.feature" with:
      """
      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Background:
          Given I have 3 apples

        Scenario: I'm little hungry
          When I ate 1 apple
          Then I should have 3 apples

        Scenario: Found more apples
          When I found 5 apples
          Then I should have 8 apples

        Scenario: Found more apples
          When I found 2 apples
          Then I should have 5 apples
          And do something undefined with $

        Scenario Outline: Other situations
          When I ate <ate> apples
          And I found <found> apples
          Then I should have <result> apples
          And do something undefined with \1

          Examples:
            | ate | found | result |
            | 3   | 1     | 1      |
            | 0   | 4     | 8      |
            | 2   | 2     | 3      |

        Scenario: Multilines
          Given pystring:
            '''
            some pystring
            '''
          And pystring 5:
            '''
            other pystring
            '''
          And table:
            | col1 | col2 |
            | val1 | val2 |
      """

  Scenario: Append snippets to main context
    When I run "behat -f progress --append-snippets"
    Then "features/bootstrap/FeatureContext.php" file should contain:
      """
      <?php

      use Behat\Behat\Context\CustomSnippetAcceptingContext,
          Behat\Behat\Tester\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeatureContext implements CustomSnippetAcceptingContext
      {
          private $apples = 0;
          private $parameters;

          public static function getAcceptedSnippetType() { return 'regex'; }

          public function __construct(array $parameters = array()) {
              $this->parameters = $parameters;
          }

          /**
           * @Given /^I have (\d+) apples?$/
           */
          public function iHaveApples($count) {
              $this->apples = intval($count);
          }

          /**
           * @When /^I ate (\d+) apples?$/
           */
          public function iAteApples($count) {
              $this->apples -= intval($count);
          }

          /**
           * @When /^I found (\d+) apples?$/
           */
          public function iFoundApples($count) {
              $this->apples += intval($count);
          }

          /**
           * @Then /^I should have (\d+) apples$/
           */
          public function iShouldHaveApples($count) {
              \PHPUnit_Framework_Assert::assertEquals(intval($count), $this->apples);
          }

          /**
           * @Then /^context parameter "([^"]*)" should be equal to "([^"]*)"$/
           */
          public function contextParameterShouldBeEqualTo($key, $val) {
              \PHPUnit_Framework_Assert::assertEquals($val, $this->parameters[$key]);
          }

          /**
           * @Given /^context parameter "([^"]*)" should be array with (\d+) elements$/
           */
          public function contextParameterShouldBeArrayWithElements($key, $count) {
              \PHPUnit_Framework_Assert::assertInternalType('array', $this->parameters[$key]);
              \PHPUnit_Framework_Assert::assertEquals(2, count($this->parameters[$key]));
          }

          private function doSomethingUndefinedWith() {}

          /**
           * @Then /^do something undefined with \$$/
           */
          public function doSomethingUndefinedWith2()
          {
              throw new PendingException();
          }

          /**
           * @Then /^do something undefined with \\(\d+)$/
           */
          public function doSomethingUndefinedWith3($arg1)
          {
              throw new PendingException();
          }

          /**
           * @Given /^pystring:$/
           */
          public function pystring(PyStringNode $string)
          {
              throw new PendingException();
          }

          /**
           * @Given /^pystring (\d+):$/
           */
          public function pystring2($arg1, PyStringNode $string)
          {
              throw new PendingException();
          }

          /**
           * @Given /^table:$/
           */
          public function table(TableNode $table)
          {
              throw new PendingException();
          }
      }
      """

  Scenario: Append snippets to main context with auto use PendingException
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\CustomSnippetAcceptingContext;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeatureContext implements CustomSnippetAcceptingContext
      {
          private $apples = 0;
          private $parameters;

          public static function getAcceptedSnippetType() { return 'regex'; }

          public function __construct(array $parameters = array()) {
              $this->parameters = $parameters;
          }

          /**
           * @Given /^I have (\d+) apples?$/
           */
          public function iHaveApples($count) {
              $this->apples = intval($count);
          }

          /**
           * @When /^I ate (\d+) apples?$/
           */
          public function iAteApples($count) {
              $this->apples -= intval($count);
          }

          /**
           * @When /^I found (\d+) apples?$/
           */
          public function iFoundApples($count) {
              $this->apples += intval($count);
          }

          /**
           * @Then /^I should have (\d+) apples$/
           */
          public function iShouldHaveApples($count) {
              \PHPUnit_Framework_Assert::assertEquals(intval($count), $this->apples);
          }

          /**
           * @Then /^context parameter "([^"]*)" should be equal to "([^"]*)"$/
           */
          public function contextParameterShouldBeEqualTo($key, $val) {
              \PHPUnit_Framework_Assert::assertEquals($val, $this->parameters[$key]);
          }

          /**
           * @Given /^context parameter "([^"]*)" should be array with (\d+) elements$/
           */
          public function contextParameterShouldBeArrayWithElements($key, $count) {
              \PHPUnit_Framework_Assert::assertInternalType('array', $this->parameters[$key]);
              \PHPUnit_Framework_Assert::assertEquals(2, count($this->parameters[$key]));
          }

          private function doSomethingUndefinedWith() {}
      }
      """
    When I run "behat -f progress --append-snippets"
    Then "features/bootstrap/FeatureContext.php" file should contain:
      """
      <?php

      use Behat\Behat\Tester\Exception\PendingException;
      use Behat\Behat\Context\CustomSnippetAcceptingContext;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeatureContext implements CustomSnippetAcceptingContext
      {
          private $apples = 0;
          private $parameters;

          public static function getAcceptedSnippetType() { return 'regex'; }

          public function __construct(array $parameters = array()) {
              $this->parameters = $parameters;
          }

          /**
           * @Given /^I have (\d+) apples?$/
           */
          public function iHaveApples($count) {
              $this->apples = intval($count);
          }

          /**
           * @When /^I ate (\d+) apples?$/
           */
          public function iAteApples($count) {
              $this->apples -= intval($count);
          }

          /**
           * @When /^I found (\d+) apples?$/
           */
          public function iFoundApples($count) {
              $this->apples += intval($count);
          }

          /**
           * @Then /^I should have (\d+) apples$/
           */
          public function iShouldHaveApples($count) {
              \PHPUnit_Framework_Assert::assertEquals(intval($count), $this->apples);
          }

          /**
           * @Then /^context parameter "([^"]*)" should be equal to "([^"]*)"$/
           */
          public function contextParameterShouldBeEqualTo($key, $val) {
              \PHPUnit_Framework_Assert::assertEquals($val, $this->parameters[$key]);
          }

          /**
           * @Given /^context parameter "([^"]*)" should be array with (\d+) elements$/
           */
          public function contextParameterShouldBeArrayWithElements($key, $count) {
              \PHPUnit_Framework_Assert::assertInternalType('array', $this->parameters[$key]);
              \PHPUnit_Framework_Assert::assertEquals(2, count($this->parameters[$key]));
          }

          private function doSomethingUndefinedWith() {}

          /**
           * @Then /^do something undefined with \$$/
           */
          public function doSomethingUndefinedWith2()
          {
              throw new PendingException();
          }

          /**
           * @Then /^do something undefined with \\(\d+)$/
           */
          public function doSomethingUndefinedWith3($arg1)
          {
              throw new PendingException();
          }

          /**
           * @Given /^pystring:$/
           */
          public function pystring(PyStringNode $string)
          {
              throw new PendingException();
          }

          /**
           * @Given /^pystring (\d+):$/
           */
          public function pystring2($arg1, PyStringNode $string)
          {
              throw new PendingException();
          }

          /**
           * @Given /^table:$/
           */
          public function table(TableNode $table)
          {
              throw new PendingException();
          }
      }
      """

    Scenario: Append snippets to two contexts
      Given a file named "features/bootstrap/FirstContext.php" with:
        """
        <?php

        use Behat\Behat\Tester\Exception\PendingException;
        use Behat\Behat\Context\CustomSnippetAcceptingContext;

        class FirstContext implements CustomSnippetAcceptingContext
        {
            public static function getAcceptedSnippetType() { return 'regex'; }
        }
        """
      And a file named "features/bootstrap/SecondContext.php" with:
        """
        <?php

        use Behat\Behat\Tester\Exception\PendingException;
        use Behat\Behat\Context\SnippetAcceptingContext;

        class SecondContext implements SnippetAcceptingContext
        {
        }
        """
      And a file named "behat.yml" with:
        """
        default:
          suites:
            first:
              contexts: [ FirstContext ]
            second:
              contexts: [ SecondContext ]
        """
      When I run "behat -f progress --append-snippets --no-colors"
      Then it should pass with:
        """
        UUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUU

        14 scenarios (14 undefined)
        58 steps (58 undefined)

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
        u features/bootstrap/SecondContext.php - `I have 3 apples` definition added
        u features/bootstrap/SecondContext.php - `I ate 1 apple` definition added
        u features/bootstrap/SecondContext.php - `I should have 3 apples` definition added
        u features/bootstrap/SecondContext.php - `I found 5 apples` definition added
        u features/bootstrap/SecondContext.php - `do something undefined with $` definition added
        u features/bootstrap/SecondContext.php - `I ate 3 apples` definition added
        u features/bootstrap/SecondContext.php - `do something undefined with \1` definition added
        u features/bootstrap/SecondContext.php - `pystring:` definition added
        u features/bootstrap/SecondContext.php - `pystring 5:` definition added
        u features/bootstrap/SecondContext.php - `table:` definition added
        """
      And "features/bootstrap/FirstContext.php" file should contain:
        """
        <?php

        use Behat\Behat\Tester\Exception\PendingException;
        use Behat\Behat\Context\CustomSnippetAcceptingContext;

        class FirstContext implements CustomSnippetAcceptingContext
        {
            public static function getAcceptedSnippetType() { return 'regex'; }

            /**
             * @Given /^I have (\d+) apples$/
             */
            public function iHaveApples($arg1)
            {
                throw new PendingException();
            }

            /**
             * @When /^I ate (\d+) apple$/
             */
            public function iAteApple($arg1)
            {
                throw new PendingException();
            }

            /**
             * @Then /^I should have (\d+) apples$/
             */
            public function iShouldHaveApples($arg1)
            {
                throw new PendingException();
            }

            /**
             * @When /^I found (\d+) apples$/
             */
            public function iFoundApples($arg1)
            {
                throw new PendingException();
            }

            /**
             * @Then /^do something undefined with \$$/
             */
            public function doSomethingUndefinedWith()
            {
                throw new PendingException();
            }

            /**
             * @When /^I ate (\d+) apples$/
             */
            public function iAteApples($arg1)
            {
                throw new PendingException();
            }

            /**
             * @Then /^do something undefined with \\(\d+)$/
             */
            public function doSomethingUndefinedWith2($arg1)
            {
                throw new PendingException();
            }

            /**
             * @Given /^pystring:$/
             */
            public function pystring(PyStringNode $string)
            {
                throw new PendingException();
            }

            /**
             * @Given /^pystring (\d+):$/
             */
            public function pystring2($arg1, PyStringNode $string)
            {
                throw new PendingException();
            }

            /**
             * @Given /^table:$/
             */
            public function table(TableNode $table)
            {
                throw new PendingException();
            }
        }
        """
      And "features/bootstrap/SecondContext.php" file should contain:
        """
        <?php

        use Behat\Behat\Tester\Exception\PendingException;
        use Behat\Behat\Context\SnippetAcceptingContext;

        class SecondContext implements SnippetAcceptingContext
        {

            /**
             * @Given I have :arg1 apples
             */
            public function iHaveApples($arg1)
            {
                throw new PendingException();
            }

            /**
             * @When I ate :arg1 apple
             */
            public function iAteApple($arg1)
            {
                throw new PendingException();
            }

            /**
             * @Then I should have :arg1 apples
             */
            public function iShouldHaveApples($arg1)
            {
                throw new PendingException();
            }

            /**
             * @When I found :arg1 apples
             */
            public function iFoundApples($arg1)
            {
                throw new PendingException();
            }

            /**
             * @Then do something undefined with $
             */
            public function doSomethingUndefinedWith()
            {
                throw new PendingException();
            }

            /**
             * @When I ate :arg1 apples
             */
            public function iAteApples($arg1)
            {
                throw new PendingException();
            }

            /**
             * @Then do something undefined with \:arg1
             */
            public function doSomethingUndefinedWith2($arg1)
            {
                throw new PendingException();
            }

            /**
             * @Given pystring:
             */
            public function pystring(PyStringNode $string)
            {
                throw new PendingException();
            }

            /**
             * @Given pystring :arg1:
             */
            public function pystring2($arg1, PyStringNode $string)
            {
                throw new PendingException();
            }

            /**
             * @Given table:
             */
            public function table(TableNode $table)
            {
                throw new PendingException();
            }
        }
        """

  Scenario: Append snippets to accepting context only
    Given a file named "features/bootstrap/FirstContext.php" with:
      """
      <?php

      use Behat\Behat\Tester\Exception\PendingException;
      use Behat\Behat\Context\CustomSnippetAcceptingContext;

      class FirstContext implements CustomSnippetAcceptingContext
      {
          public static function getAcceptedSnippetType() { return 'regex'; }
      }
      """
    And a file named "features/bootstrap/SecondContext.php" with:
      """
      <?php

      use Behat\Behat\Tester\Exception\PendingException;
      use Behat\Behat\Context\Context;

      class SecondContext implements Context
      {
      }
      """
    And a file named "behat.yml" with:
      """
      default:
        suites:
          first:
            contexts: [ FirstContext ]
          second:
            contexts: [ SecondContext ]
      """
    When I run "behat -f progress --append-snippets --no-colors"
    Then it should pass with:
      """
      UUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUUU

      14 scenarios (14 undefined)
      58 steps (58 undefined)

      --- Snippets for the following steps in the second suite were not generated (check your configuration):

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

      use Behat\Behat\Tester\Exception\PendingException;
      use Behat\Behat\Context\CustomSnippetAcceptingContext;

      class FirstContext implements CustomSnippetAcceptingContext
      {
          public static function getAcceptedSnippetType() { return 'regex'; }

          /**
           * @Given /^I have (\d+) apples$/
           */
          public function iHaveApples($arg1)
          {
              throw new PendingException();
          }

          /**
           * @When /^I ate (\d+) apple$/
           */
          public function iAteApple($arg1)
          {
              throw new PendingException();
          }

          /**
           * @Then /^I should have (\d+) apples$/
           */
          public function iShouldHaveApples($arg1)
          {
              throw new PendingException();
          }

          /**
           * @When /^I found (\d+) apples$/
           */
          public function iFoundApples($arg1)
          {
              throw new PendingException();
          }

          /**
           * @Then /^do something undefined with \$$/
           */
          public function doSomethingUndefinedWith()
          {
              throw new PendingException();
          }

          /**
           * @When /^I ate (\d+) apples$/
           */
          public function iAteApples($arg1)
          {
              throw new PendingException();
          }

          /**
           * @Then /^do something undefined with \\(\d+)$/
           */
          public function doSomethingUndefinedWith2($arg1)
          {
              throw new PendingException();
          }

          /**
           * @Given /^pystring:$/
           */
          public function pystring(PyStringNode $string)
          {
              throw new PendingException();
          }

          /**
           * @Given /^pystring (\d+):$/
           */
          public function pystring2($arg1, PyStringNode $string)
          {
              throw new PendingException();
          }

          /**
           * @Given /^table:$/
           */
          public function table(TableNode $table)
          {
              throw new PendingException();
          }
      }
      """
    And "features/bootstrap/SecondContext.php" file should contain:
      """
      <?php

      use Behat\Behat\Tester\Exception\PendingException;
      use Behat\Behat\Context\Context;

      class SecondContext implements Context
      {
      }
      """
