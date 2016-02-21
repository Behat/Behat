@php-version @php7
Feature: Support PHP 7 Throwable
  In order for my test suite to continue running despite fatal errors in my code
  As a feature developer
  I need Behat to gracefully handle errors implementing the Throwable interface

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
           * @When /^I have some code with a fatal error$/
           */
          public function iHaveSomeCodeWithFatalError()
          {
              ("not an object")->method();
          }

          /**
           * @Then /^I should be skipped$/
           */
          public function iShouldBeSkipped()
          {
          }
      }
      """
    And a file named "features/fatal_errors.feature" with:
      """
      Feature: Fatal error in scenario
        In order to test the handling of the PHP 7 Throwable interface
        As a contributor of Behat
        I need to have a FeatureContext that contains errors that were fatal in previous PHP versions

        Scenario: Handling of a fatal error
          When I have some code with a fatal error
          Then I should be skipped
      """

  Scenario: Handling of a fatal error
    When I run "behat --no-colors"
    Then it should fail
    And the output should contain:
      """
        Scenario: Handling of a fatal error        # features/fatal_errors.feature:6
          When I have some code with a fatal error # FeatureContext::iHaveSomeCodeWithFatalError()
            Fatal error: Call to a member function method() on string (Behat\Testwork\Call\Exception\FatalThrowableError)
          Then I should be skipped                 # FeatureContext::iShouldBeSkipped()

      --- Failed scenarios:

          features/fatal_errors.feature:6

      1 scenario (1 failed)
      2 steps (1 failed, 1 skipped)
      """
