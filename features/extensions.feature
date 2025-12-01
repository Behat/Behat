Feature: Extensions
  In order to provide additional functionality for Behat
  As a developer
  I need to be able to write simple extensions

  Background:
    Given I initialise the working directory from the "Extensions" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value    |
      | --no-colors |          |
      | --format    | pretty   |

  Scenario: Extension should be successfully loaded
    When I run "behat features/extensions.feature"
    Then it should pass with:
      """
      Feature:
      
        Scenario:                             # features/extensions.feature:2
          When this scenario executes         # FeatureContext::thisScenarioExecutes()
          Then the extension should be loaded # FeatureContext::theExtensionLoaded()
      
      1 scenario (1 passed)
      2 steps (2 passed)
      """

  Scenario: Instantiating inexistent extension file
    When I run behat with the following additional options:
      | option   | value                 |
      | --config | behat-inexistent.yaml |
    Then it should fail with:
      """
      `inexistent_extension` extension file or class could not be located.
      """

  Scenario: Exception handlers extension
    When I run behat with the following additional options:
      | option                              | value                         |
      | --config                            | behat-exception-handlers.yaml |
      | features/exception_handlers.feature |                               |
    Then it should fail with:
      """
      Feature:
      
        Scenario:                  # features/exception_handlers.feature:2
          Given non-existent class # ExceptionHandlerContext::nonexistentClass()
            Fatal error: Class "Non\Existent\Cls" not found (Behat\Testwork\Call\Exception\FatalThrowableError)
      
        Scenario:                   # features/exception_handlers.feature:4
          Given non-existent method # ExceptionHandlerContext::nonexistentMethod()
            Fatal error: Call to undefined method ExceptionHandlerContext::getName() (Behat\Testwork\Call\Exception\FatalThrowableError)
      
      --- Failed scenarios:
      
          features/exception_handlers.feature:2 (on line 3)
          features/exception_handlers.feature:4 (on line 5)
      
      2 scenarios (2 failed)
      2 steps (2 failed)
      string(16) "Non\Existent\Cls"
      array(2) {
        [0]=>
        string(23) "ExceptionHandlerContext"
        [1]=>
        string(7) "getName"
      }
      """
