Feature: Gherkin compatibility mode
  In order to have my feature files parsed as they would be by other cucumber-based tools
  As a tester
  I need to be able to configure the Gherkin parser compatibility mode

  Background:
    Given I initialise the working directory from the GherkinCompatibility fixtures folder
    And   I provide the following options for all behat invocations:
      | option      | value    |
      | --no-colors |          |
      | --format    | progress |

  @gherkin-mode:has-explicit
  Scenario: Legacy parsing by default (ignores invalid language)
    Given the test runner does not configure a default gherkin compatibility mode
    When I run behat with the following additional options:
      | option    | value   |
      | --profile | default |
    Then it should pass with:
      """
      UUU

      1 scenario (1 undefined)
      3 steps (3 undefined)
      """

  @gherkin-mode:has-explicit
  Scenario: Opt in to cucumber-compatible parsing
    When I run behat with the following additional options:
      | option    | value      |
      | --profile | gherkin-32 |
    Then it should fail with:
      """
      In KeywordsDialectProvider.php line XX:

        Language not supported: martian
      """
