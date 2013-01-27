Feature: Append snippets option
  In order to use definition snippets fully
  As a context developer
  I need to be able to autoappend snippets to context

  Background:
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      require_once 'PHPUnit/Autoload.php';
      require_once 'PHPUnit/Framework/Assert/Functions.php';

      use Behat\Behat\Context\BehatContext,
          Behat\Behat\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeatureContext extends BehatContext
      {
          private $apples = 0;
          private $parameters;

          public function __construct(array $parameters) {
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
              assertEquals(intval($count), $this->apples);
          }

          /**
           * @Then /^context parameter "([^"]*)" should be equal to "([^"]*)"$/
           */
          public function contextParameterShouldBeEqualTo($key, $val) {
            assertEquals($val, $this->parameters[$key]);
          }

          /**
           * @Given /^context parameter "([^"]*)" should be array with (\d+) elements$/
           */
          public function contextParameterShouldBeArrayWithElements($key, $count) {
              assertInternalType('array', $this->parameters[$key]);
              assertEquals(2, count($this->parameters[$key]));
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
    And "features/bootstrap/FeatureContext.php" file should contain:
      """
      <?php

      require_once 'PHPUnit/Autoload.php';
      require_once 'PHPUnit/Framework/Assert/Functions.php';

      use Behat\Behat\Context\BehatContext,
          Behat\Behat\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeatureContext extends BehatContext
      {
          private $apples = 0;
          private $parameters;

          public function __construct(array $parameters) {
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
              assertEquals(intval($count), $this->apples);
          }

          /**
           * @Then /^context parameter "([^"]*)" should be equal to "([^"]*)"$/
           */
          public function contextParameterShouldBeEqualTo($key, $val) {
            assertEquals($val, $this->parameters[$key]);
          }

          /**
           * @Given /^context parameter "([^"]*)" should be array with (\d+) elements$/
           */
          public function contextParameterShouldBeArrayWithElements($key, $count) {
              assertInternalType('array', $this->parameters[$key]);
              assertEquals(2, count($this->parameters[$key]));
          }

          private function doSomethingUndefinedWith() {}

          /**
           * @Given /^do something undefined with \$$/
           */
          public function doSomethingUndefinedWith2()
          {
              throw new PendingException();
          }

          /**
           * @Given /^do something undefined with \\(\d+)$/
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

  Scenario: Append snippets to custom class
    Given a file named "features/bootstrap/AdditionalContext.php" with:
      """
      <?php

      use Behat\Behat\Context\BehatContext;

      class AdditionalContext extends BehatContext
      {
      }
      """
    When I run "behat -f progress --append-to=AdditionalContext"
    Then "features/bootstrap/AdditionalContext.php" file should contain:
      """
      <?php

      use Behat\Behat\Context\BehatContext;

      class AdditionalContext extends BehatContext
      {

          /**
           * @Given /^do something undefined with \$$/
           */
          public function doSomethingUndefinedWith2()
          {
              throw new PendingException();
          }

          /**
           * @Given /^do something undefined with \\(\d+)$/
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
