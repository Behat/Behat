Feature: Context consistency
  In order to maintain stable behavior tests
  As a feature writer
  I need a separate context for every scenario/outline

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
    And a file named "features/bootstrap/CustomContext.php" with:
      """
      <?php

      use Behat\Behat\Context\BehatContext;

      class CustomContext extends BehatContext
      {
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
    When I run "behat --no-ansi -f progress features/apples.feature"
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
    When I run "behat --no-ansi -f progress features/apples.feature"
    Then it should fail with:
      """
      ..F..F...F.......F

      (::) failed steps (::)

      01. Failed asserting that 2 matches expected 5.
          In step `Then I should have 5 apples'. # FeatureContext::iShouldHaveApples()
          From scenario `I'm little hungry'.     # features/apples.feature:9
          Of feature `Apples story'.             # features/apples.feature

      02. Failed asserting that 13 matches expected 10.
          In step `Then I should have 10 apples'. # FeatureContext::iShouldHaveApples()
          From scenario `Found more apples'.      # features/apples.feature:13
          Of feature `Apples story'.              # features/apples.feature

      03. Failed asserting that 1 matches expected 3.
          In step `Then I should have 3 apples'.  # FeatureContext::iShouldHaveApples()
          From scenario `Other situations'.       # features/apples.feature:17
          Of feature `Apples story'.              # features/apples.feature

      04. Failed asserting that 3 matches expected 4.
          In step `Then I should have 4 apples'.  # FeatureContext::iShouldHaveApples()
          From scenario `Other situations'.       # features/apples.feature:17
          Of feature `Apples story'.              # features/apples.feature

      5 scenarios (1 passed, 4 failed)
      18 steps (14 passed, 4 failed)
      """

  Scenario: Context parameters
    Given a file named "behat.yml" with:
      """
      default:
        context:
          parameters:
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
  When I run "behat --no-ansi -f progress features/params.feature"
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
        context:
          class: CustomContext
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
  When I run "behat --no-ansi -f progress features/params.feature"
  Then it should pass with:
    """
    UU

    1 scenario (1 undefined)
    2 steps (2 undefined)

    You can implement step definitions for undefined steps with these snippets:

        /**
         * @Then /^context parameter "([^"]*)" should be equal to "([^"]*)"$/
         */
        public function contextParameterShouldBeEqualTo($arg1, $arg2)
        {
            throw new PendingException();
        }

        /**
         * @Given /^context parameter "([^"]*)" should be array with (\d+) elements$/
         */
        public function contextParameterShouldBeArrayWithElements($arg1, $arg2)
        {
            throw new PendingException();
        }
    """

  Scenario: Unexisting custom context class
    Given a file named "behat.yml" with:
      """
      default:
        context:
          class: UnexistentContext
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
  When I run "behat --no-ansi -f progress features/params.feature"
  Then it should fail with:
    """
    [RuntimeException]
      Context class "UnexistentContext" not found and can not be instantiated.



    behat [--init] [-f|--format="..."] [--out="..."] [--lang="..."] [--[no-]ansi] [--[no-]time] [--[no-]paths] [--[no-]snippets] [--[no-]snippets-paths] [--[no-]multiline] [--[no-]expand] [--story-syntax] [-d|--definitions="..."] [--name="..."] [--tags="..."] [--cache="..."] [--strict] [--dry-run] [--stop-on-failure] [--rerun="..."] [--append-snippets] [--append-to="..."] [features]
    """
