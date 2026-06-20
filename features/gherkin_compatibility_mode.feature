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
  Scenario: Cucumber-compatible parsing by default (fails on invalid language)
    Given the test runner does not configure a default gherkin compatibility mode
    When I run behat with the following additional options:
      | option    | value   |
      | --profile | default |
    Then it should fail with:
      """
      In KeywordsDialectProvider.php line XX:

        Language not supported: martian
      """
  
    
  @gherkin-mode:has-explicit
  Scenario: Opt in to legacy parsing (ignores invalid language)
    When I run behat with the following additional options:
      | option    | value          |
      | --profile | gherkin-legacy |
    Then it should pass with:
      """
      UUU

      1 scenario (1 undefined)
      3 steps (3 undefined)
      """
