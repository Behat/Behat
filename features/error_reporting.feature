Feature: Error Reporting
  In order to ignore E_NOTICE warnings of code I depend uppon
  As a feature developer
  I need to have an ability to set a custom error level for steps to be executed in

  Background:
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context,
          Behat\Behat\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeatureContext implements Context
      {
          /**
           * @Given /^I have an empty array$/
           */
          public function iHaveAnEmptyArray()
          {
              $this->array = array();
          }

          /**
           * @When /^I access array index (\d+)$/
           */
          public function iAccessArrayIndex($arg1)
          {
              $index = intval($arg1);
              $this->result = $this->array[$index];
          }

          /**
           * @Then /^I should get NULL$/
           */
          public function iShouldGetNull()
          {
              PHPUnit_Framework_Assert::assertNull($this->result);
          }

          /**
           * @When /^I push "([^"]*)" to that array$/
           */
          public function iPushToThatArray($arg1)
          {
              array_push($this->array, $arg1);
          }

          /**
           * @Then /^I should get "([^"]*)"$/
           */
          public function iShouldGet($arg1)
          {
              PHPUnit_Framework_Assert::assertEquals($arg1, $this->result);
          }

      }
      """
    And a file named "features/e_notice_in_scenario.feature" with:
      """
      Feature: E_NOTICE in scenario
        In order to test the BEHAT_ERROR_REPORTING constant
        As a contributor of behat
        I need to have a FeatureContext that throws E_NOTICE within steps.

        Background:
          Given I have an empty array

        Scenario: Access undefined index
          When I access array index 0
          Then I should get NULL

        Scenario: Access defined index
          When I push "foo" to that array
          And I access array index 0
          Then I should get "foo"

      """

  Scenario: With default error reporting
    When I run "behat -f progress --no-colors"
    Then it should fail
    And the output should contain:
    """
    --- Failed steps:

        When I access array index 0 # features/e_notice_in_scenario.feature:10
          Notice: Undefined offset: 0 in features/bootstrap/FeatureContext.php line 24

    2 scenarios (1 passed, 1 failed)
    7 steps (5 passed, 1 failed, 1 skipped)
    """

  Scenario: With error reporting ignoring E_NOTICE
    Given a file named "behat.yml" with:
      """
      default:
        calls:
          error_reporting: 32759
      """
    When I run "behat -f progress --no-colors"
    Then it should pass
