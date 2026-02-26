Feature: Unsuppressed deprecation in step

  Scenario: A scenario with an unsuppressed deprecation
    Given I run a step that triggers an unsuppressed deprecation
    Then the step should pass
