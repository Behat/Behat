Feature: Boolean options via Environment Variable
  In order to set boolean options via BEHAT_PARAMS
  As a Behat user
  I need Behat to convert the string value into boolean

  Background:
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\BehatContext;

      class FeatureContext extends BehatContext
      {
      }
      """
    And a file named "features/coffee.feature" with:
      """
      Feature: Undefined coffee machine actions
        In order to make clients happy
        As a coffee machine factory
        We need to be able to tell customers
        about what coffe type is supported

        Background:
          Given I have magically created 10$

        Scenario: Buy incredible coffee
          When I have chosen "coffee with turkey" in coffee machine
          Then I should have "turkey with coffee sauce"

        Scenario: Buy incredible tea
          When I have chosen "pizza tea" in coffee machine
          Then I should have "pizza tea"
      """

  Scenario Outline: True-ish strict option
    Given "BEHAT_PARAMS" environment variable is set to:
      """
      options[strict]=<strict>&formatter[parameters][time]=false
      """
    When I run "behat --no-ansi -f progress features/coffee.feature"
    Then it should fail with:
      """
      UUUUUU

2 scenarios (2 undefined)
6 steps (6 undefined)

You can implement step definitions for undefined steps with these snippets:

          /**
           * @Given /^I have magically created (\d+)\$$/
           */
          public function iHaveMagicallyCreated($arg1)
          {
              throw new PendingException();
          }

          /**
           * @When /^I have chosen "([^"]*)" in coffee machine$/
           */
          public function iHaveChosenInCoffeeMachine($arg1)
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
    Examples:
      | strict |
      | true   |
      | on     |
      | yes    |
      | 1      |

  Scenario Outline: False-ish strict option
    Given "BEHAT_PARAMS" environment variable is set to:
      """
      options[strict]=<strict>&formatter[parameters][time]=false
      """
    When I run "behat --no-ansi -f progress features/coffee.feature"
    Then it should pass with:
      """
      UUUUUU

2 scenarios (2 undefined)
6 steps (6 undefined)

You can implement step definitions for undefined steps with these snippets:

          /**
           * @Given /^I have magically created (\d+)\$$/
           */
          public function iHaveMagicallyCreated($arg1)
          {
              throw new PendingException();
          }

          /**
           * @When /^I have chosen "([^"]*)" in coffee machine$/
           */
          public function iHaveChosenInCoffeeMachine($arg1)
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
    Examples:
      | strict |
      | false  |
      | off    |
      | no     |
      | 0      |

  Scenario Outline: Non-boolish strict option
    Given "BEHAT_PARAMS" environment variable is set to:
      """
      options[strict]=<strict>&formatter[parameters][time]=false
      """
    When I run "behat --no-ansi -f progress features/coffee.feature"
    Then it should fail with:
      """
      [Symfony\Component\Config\Definition\Exception\InvalidTypeException]
        Invalid type for path "behat.options.strict". Expected boolean, but got string.
      """
    Examples:
      | strict    |
      | null      |
      | dont-care |
