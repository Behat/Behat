Feature: Deprecations in steps and hooks

  @deprecation-in-hook
  Scenario: A scenario with deprecations in steps and hooks
    Given I run a step that triggers a deprecation
    Then the step should pass
