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
          private $array;
          private $result;

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
              PHPUnit\Framework\Assert::assertNull($this->result);
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
              PHPUnit\Framework\Assert::assertEquals($arg1, $this->result);
          }

          /**
           * @When an exception is thrown
           */
          public function anExceptionIsThrown()
          {
              throw new \Exception('Exception is thrown');
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
    And a file named "features/exception_in_scenario.feature" with:
      """
      Feature: Error in scenario
        In order to test the error stacktraces
        As a contributor of behat
        I need to have a FeatureContext that triggers an error within steps.

        Scenario: Exception thrown
          When an exception is thrown

      """

  Scenario: With default error reporting
    When I run "behat -f progress --no-colors features/e_notice_in_scenario.feature"
    Then it should fail
    And the output should contain:
    """
    --- Failed steps:

    001 Scenario: Access undefined index # features/e_notice_in_scenario.feature:9
          When I access array index 0    # features/e_notice_in_scenario.feature:10
            Notice: Undefined offset: 0 in features/bootstrap/FeatureContext.php line 27

    2 scenarios (1 passed, 1 failed)
    7 steps (5 passed, 1 failed, 1 skipped)
    """

  Scenario: With error reporting ignoring E_NOTICE and E_WARNING
    Given a file named "behat.yml" with:
      """
      default:
        calls:
          error_reporting: 32757
      """
    When I run "behat -f progress --no-colors features/e_notice_in_scenario.feature"
    Then it should pass

  Scenario: With very verbose error reporting
    When I run "behat -f progress --no-colors -vv features/exception_in_scenario.feature"
    Then it should fail
    And the output should contain:
    """
    --- Failed steps:

    001 Scenario: Exception thrown    # features/exception_in_scenario.feature:6
          When an exception is thrown # features/exception_in_scenario.feature:7
            Exception: Exception is thrown in features/bootstrap/FeatureContext.php:59
            Stack trace:

    1 scenario (1 failed)
    1 step (1 failed)
    """

  Scenario: With debug verbose error reporting
    When I run "behat -f progress --no-colors -vvv features/exception_in_scenario.feature"
    Then it should fail
    And the output should contain:
    """
    --- Failed steps:

    001 Scenario: Exception thrown    # features/exception_in_scenario.feature:6
          When an exception is thrown # features/exception_in_scenario.feature:7
            Exception: Exception is thrown in features/bootstrap/FeatureContext.php:59
            Stack trace:
            #0 src/Behat/Testwork/Call/Handler/RuntimeCallHandler.php(110): FeatureContext->anExceptionIsThrown()
            #1 src/Behat/Testwork/Call/Handler/RuntimeCallHandler.php(64): Behat\Testwork\Call\Handler\RuntimeCallHandler->executeCall(
    """
