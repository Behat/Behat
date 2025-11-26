Feature: Support PHP 7 Throwable
  In order for my test suite to continue running despite fatal errors in my code
  As a feature developer
  I need Behat to gracefully handle errors implementing the Throwable interface

  Background:
    Given I initialise the working directory from the "CatchThrowable" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value |
      | --no-colors |       |

  Scenario: Handling of a fatal error
    When I run "behat features/fatal_errors.feature"
    Then it should fail
    And the output should contain:
      """
        Scenario: Handling of a fatal error        # features/fatal_errors.feature:6
          When I have some code with a fatal error # FeatureContext::iHaveSomeCodeWithFatalError()
            Fatal error: Call to a member function method() on string (Behat\Testwork\Call\Exception\FatalThrowableError)
          Then I should be skipped                 # FeatureContext::iShouldBeSkipped()

      --- Failed scenarios:

          features/fatal_errors.feature:6 (on line 7)

      1 scenario (1 failed)
      2 steps (1 failed, 1 skipped)
      """
