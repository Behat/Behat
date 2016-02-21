Feature: Context consistency
  In order to maintain stable behavior tests
  As a feature writer
  I need a separate context for every scenario/outline

  Background:
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\CustomSnippetAcceptingContext,
          Behat\Behat\Tester\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class CoreContext
      {
          protected $apples = 0;
          protected $parameters;

          public function __construct($parameter2 = 'val2_default', $parameter1 = 'val1_default') {
              $this->parameters = array('parameter1' => $parameter1, 'parameter2' => $parameter2);
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
              PHPUnit_Framework_Assert::assertEquals(intval($count), $this->apples);
          }

          /**
           * @Then /^context parameter "([^"]*)" should be equal to "([^"]*)"$/
           */
          public function contextParameterShouldBeEqualTo($key, $val) {
              PHPUnit_Framework_Assert::assertEquals($val, $this->parameters[$key]);
          }

          /**
           * @Given /^context parameter "([^"]*)" should be array with (\d+) elements$/
           */
          public function contextParameterShouldBeArrayWithElements($key, $count) {
              PHPUnit_Framework_Assert::assertInternalType('array', $this->parameters[$key]);
              PHPUnit_Framework_Assert::assertEquals(2, count($this->parameters[$key]));
          }
      }

      class FeatureContext extends CoreContext implements CustomSnippetAcceptingContext
      {
          public static function getAcceptedSnippetType() { return 'regex'; }
      }
      """
    And a file named "features/bootstrap/CustomContext.php" with:
      """
      <?php

      use Behat\Behat\Context\CustomSnippetAcceptingContext,
          Behat\Behat\Tester\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class CustomContext implements CustomSnippetAcceptingContext
      {
          public static function getAcceptedSnippetType() { return 'regex'; }
      }
      """

  Scenario: True "apples story"
    Given a file named "features/apples.feature" with:
      """
      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Background:
          Given I have 3 apples

        Scenario: I'm little hungry
          When I ate 1 apple
          Then I should have 2 apples

        Scenario: Found more apples
          When I found 2 apples
          Then I should have 5 apples

        Scenario Outline: Other situations
          When I ate <ate> apples
          And I found <found> apples
          Then I should have <result> apples

          Examples:
            | ate | found | result |
            | 3   | 1     | 1      |
            | 0   | 5     | 8      |
            | 2   | 2     | 3      |
      """
    When I run "behat --no-colors -f progress features/apples.feature"
    Then it should pass with:
      """
      ..................

      5 scenarios (5 passed)
      18 steps (18 passed)
      """

  Scenario: False "apples story"
    Given a file named "features/apples.feature" with:
      """
      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Background:
          Given I have 3 apples

        Scenario: I'm little hungry
          When I ate 1 apple
          Then I should have 5 apples

        Scenario: Found more apples
          When I found 10 apples
          Then I should have 10 apples

        Scenario Outline: Other situations
          When I ate <ate> apples
          And I found <found> apples
          Then I should have <result> apples

          Examples:
            | ate | found | result |
            | 3   | 1     | 3      |
            | 0   | 5     | 8      |
            | 2   | 2     | 4      |
      """
    When I run "behat --no-colors -f progress features/apples.feature"
    Then it should fail with:
      """
      ..F..F...F.......F

      --- Failed steps:

          Then I should have 5 apples # features/apples.feature:11
            Failed asserting that 2 matches expected 5.

          Then I should have 10 apples # features/apples.feature:15
            Failed asserting that 13 matches expected 10.

          Then I should have 3 apples # features/apples.feature:20
            Failed asserting that 1 matches expected 3.

          Then I should have 4 apples # features/apples.feature:20
            Failed asserting that 3 matches expected 4.

      5 scenarios (1 passed, 4 failed)
      18 steps (14 passed, 4 failed)
      """

  Scenario: Context parameters
    Given a file named "behat.yml" with:
      """
      default:
        suites:
          default:
            contexts:
              - FeatureContext:
                  parameter1: val_one
                  parameter2:
                    everzet: behat_admin
                    avalanche123: behat_admin
      """
    And a file named "features/params.feature" with:
      """
      Feature: Context parameters
        In order to run a browser
        As feature runner
        I need to be able to configure behat context

        Scenario: I'm little hungry
          Then context parameter "parameter1" should be equal to "val_one"
          And context parameter "parameter2" should be array with 2 elements
      """
    When I run "behat --no-colors -f progress features/params.feature"
    Then it should pass with:
      """
      ..

      1 scenario (1 passed)
      2 steps (2 passed)
      """

  Scenario: Context parameters including optional
    Given a file named "behat.yml" with:
      """
      default:
        suites:
          default:
            contexts:
              - FeatureContext:
                  parameter1: val_one
      """
    And a file named "features/params.feature" with:
      """
      Feature: Context parameters
        In order to run a browser
        As feature runner
        I need to be able to configure behat context

        Scenario: I'm little hungry
          Then context parameter "parameter1" should be equal to "val_one"
          Then context parameter "parameter2" should be equal to "val2_default"
      """
    When I run "behat --no-colors -f progress features/params.feature"
    Then it should pass with:
      """
      ..

      1 scenario (1 passed)
      2 steps (2 passed)
      """

  Scenario: Existing custom context class
    Given a file named "behat.yml" with:
      """
      default:
        suites:
          default:
            contexts: [ CustomContext ]
      """
    And a file named "features/params.feature" with:
      """
      Feature: Context parameters
        In order to run a browser
        As feature runner
        I need to be able to configure behat context

        Scenario: I'm little hungry
          Then context parameter "parameter1" should be equal to "val_one"
          And context parameter "parameter2" should be array with 2 elements
      """
  When I run "behat --no-colors -f progress features/params.feature"
  Then it should pass with:
    """
    UU

    1 scenario (1 undefined)
    2 steps (2 undefined)

    --- CustomContext has missing steps. Define them with these snippets:

        /**
         * @Then /^context parameter "([^"]*)" should be equal to "([^"]*)"$/
         */
        public function contextParameterShouldBeEqualTo($arg1, $arg2)
        {
            throw new PendingException();
        }

        /**
         * @Then /^context parameter "([^"]*)" should be array with (\d+) elements$/
         */
        public function contextParameterShouldBeArrayWithElements($arg1, $arg2)
        {
            throw new PendingException();
        }
    """

  Scenario: Single context class instead of an array provided as `contexts` option
    Given a file named "behat.yml" with:
      """
      default:
        suites:
          default:
            contexts: UnexistentContext
      """
    And a file named "features/params.feature" with:
      """
      Feature: Context parameters
        In order to run a browser
        As feature runner
        I need to be able to configure behat context

        Scenario: I'm little hungry
          Then context parameter "parameter1" should be equal to "val_one"
          And context parameter "parameter2" should be array with 2 elements
      """
    When I run "behat --no-colors -f progress features/params.feature"
    Then it should fail with:
      """
      [Behat\Testwork\Suite\Exception\SuiteConfigurationException]
        `contexts` setting of the "default" suite is expected to be an array, string given.
      """

  Scenario: Unexisting custom context class
    Given a file named "behat.yml" with:
      """
      default:
        suites:
          default:
            contexts: [ UnexistentContext ]
      """
    And a file named "features/params.feature" with:
      """
      Feature: Context parameters
        In order to run a browser
        As feature runner
        I need to be able to configure behat context

        Scenario: I'm little hungry
          Then context parameter "parameter1" should be equal to "val_one"
          And context parameter "parameter2" should be array with 2 elements
      """
  When I run "behat --no-colors -f progress features/params.feature"
  Then it should fail with:
    """
    [Behat\Behat\Context\Exception\ContextNotFoundException]
      `UnexistentContext` context class not found and can not be used.
    """

  Scenario: Unexisting context argument
    Given a file named "behat.yml" with:
      """
      default:
        suites:
          default:
            contexts:
              - FeatureContext:
                  unexistingParam: 'value'
      """
    And a file named "features/params.feature" with:
      """
      Feature: Context parameters
        In order to run a browser
        As feature runner
        I need to be able to configure behat context

        Scenario: I'm little hungry
          Then context parameter "parameter1" should be equal to "val_one"
          And context parameter "parameter2" should be array with 2 elements
      """
    When I run "behat --no-colors -f progress features/params.feature"
    Then it should fail with:
      """
      [Behat\Testwork\Argument\Exception\UnknownParameterValueException]
        `CoreContext::__construct()` does not expect argument `$unexistingParam`.
      """

  Scenario: Suite without contexts and FeatureContext available
    Given a file named "behat.yml" with:
      """
      default:
        suites:
          first:
            contexts: []
      """
    And a file named "features/some.feature" with:
      """
      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Scenario: I'm little hungry
          Given I have 3 apples
          When I ate 1 apple
          Then I should have 2 apples
      """
    When I run "behat --no-colors -fpretty --format-settings='{\"paths\": true}' features"
    Then it should pass with:
      """
      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Scenario: I'm little hungry   # features/some.feature:6
          Given I have 3 apples
          When I ate 1 apple
          Then I should have 2 apples

      1 scenario (1 undefined)
      3 steps (3 undefined)

      --- Snippets for the following steps in the first suite were not generated (does your context implement SnippetAcceptingContext interface?):

          Given I have 3 apples
          When I ate 1 apple
          Then I should have 2 apples
      """

  Scenario: Suite with custom context and FeatureContext available
    Given a file named "behat.yml" with:
      """
      default:
        suites:
          first:
            contexts:
              - CustomContext
      """
    And a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php use Behat\Behat\Context\Context;

      class FeatureContext implements Context {
          /** @BeforeScenario */
          public function beforeScenario() {
              echo "Setting up";
          }

          /** @Given step */
          public function step() {}
      }
      """
    And a file named "features/bootstrap/CustomContext.php" with:
      """
      <?php class CustomContext extends FeatureContext {}
      """
    And a file named "features/some.feature" with:
      """
      Feature:
        Scenario:
          Given step
        Scenario:
          Given step
      """
    When I run "behat --no-colors -fpretty --format-settings='{\"paths\": true}' features"
    Then it should pass with:
      """
      Feature:

        ┌─ @BeforeScenario # CustomContext::beforeScenario()
        │
        │  Setting up
        │
        Scenario:    # features/some.feature:2
          Given step # CustomContext::step()

        ┌─ @BeforeScenario # CustomContext::beforeScenario()
        │
        │  Setting up
        │
        Scenario:    # features/some.feature:4
          Given step # CustomContext::step()

      2 scenarios (2 passed)
      2 steps (2 passed)
      """
