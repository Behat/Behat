Feature: Unused definitions
  In order to remove unused code
  As a feature developer
  I need to be able to generate a list of unused definitions

  Background:
    Given I initialise the working directory from the "UnusedDefinitions" fixtures folder
    And I provide the following options for all behat invocations:
      | option                     | value    |
      | --no-colors                |          |
      | --format                   | progress |

  Scenario: Print unused definitions for a single suite
    When I run behat with the following additional options:
      | option                     | value       |
      | --suite                    | first_suite |
      | --print-unused-definitions |             |
    Then it should pass with:
      """
      --- 2 unused definitions:

      [Then|*] I call a step used in the second feature
      `FeatureContext::stepUsedInSecondFeature()`

      [Then|*] I call a step not used in any feature
      This is a step that is never used and should be removed.
      `FeatureContext::stepNotUsedInAnyFeature()`
      """

  Scenario: Print unused definitions for two suites
    When I run behat with the following additional options:
      | option                     | value       |
      | --print-unused-definitions |             |
    Then it should pass with:
      """
      --- 1 unused definition:

      [Then|*] I call a step not used in any feature
      This is a step that is never used and should be removed.
      `FeatureContext::stepNotUsedInAnyFeature()`
      """

  Scenario: Print unused definitions from PHP config
    When I run behat with the following additional options:
      | option    | value              |
      | --profile | unused_definitions |
    Then it should pass with:
      """
      --- 1 unused definition:

      [Then|*] I call a step not used in any feature
      This is a step that is never used and should be removed.
      `FeatureContext::stepNotUsedInAnyFeature()`
      """

  Scenario: Print unused definitions when using translated definitions
    When I run behat with the following additional options:
      | option                     | value                   |
      | --profile                  | translated_definitions  |
      | --suite                    | translated_definitions  |
      | --print-unused-definitions |                         |
    Then it should pass with:
      """
      --- 1 unused definition:

      [Given|*] /^I have clicked "+"$/
      `TranslatedDefinitionsContext::iHaveClickedPlus()`
      """
