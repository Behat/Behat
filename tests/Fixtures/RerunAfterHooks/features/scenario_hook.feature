Feature: After scenario hooks

  @failing-after-scenario-hook
  Scenario: all steps pass but the after-scenario hook throws
    Given a step that passes

  Scenario: everything passes
    Given a step that passes
