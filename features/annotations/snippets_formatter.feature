Feature: Snippets format
  In order to see only undefined steps snippets
  As a tester
  I need to be able to use 'snippets' formatter

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
          And do something undefined

        Scenario Outline: Other situations
          When I ate <ate> apples
          And I found <found> apples
          Then I should have <result> apples

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
          And table:
            | col1 | col2 |
            | val1 | val2 |

        Scenario: Worded quote
          When que j'utilise behat en français'
          Then j'utilise un apostrophe et j'obtiens une erreur
          And some 'properly escaped' string
          And 'another escaped' string
          And one 'more string'
          And one "more string"
          And one percentage 10%
      """

  Scenario: Run feature with failing scenarios
    When I run "behat --no-ansi -f snippets"
    Then it should fail with:
      """
      /**
           * @Given /^do something undefined$/
           */
          public function doSomethingUndefined()
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
           * @Given /^table:$/
           */
          public function table(TableNode $table)
          {
              throw new PendingException();
          }

          /**
           * @When /^que j\'utilise behat en français\'$/
           */
          public function queJUtiliseBehatEnFrancais()
          {
              throw new PendingException();
          }

          /**
           * @Then /^j\'utilise un apostrophe et j\'obtiens une erreur$/
           */
          public function jUtiliseUnApostropheEtJObtiensUneErreur()
          {
              throw new PendingException();
          }

          /**
           * @Given /^some \'([^\']*)\' string$/
           */
          public function someString($arg1)
          {
              throw new PendingException();
          }

          /**
           * @Given /^\'([^\']*)\' string$/
           */
          public function string($arg1)
          {
              throw new PendingException();
          }

          /**
           * @Given /^one \'([^\']*)\'$/
           */
          public function one($arg1)
          {
              throw new PendingException();
          }

          /**
           * @Given /^one "([^"]*)"$/
           */
          public function one2($arg1)
          {
              throw new PendingException();
          }

          /**
           * @Given /^one percentage (\d+)%$/
           */
          public function onePercentage($arg1)
          {
              throw new PendingException();
          }
        """
