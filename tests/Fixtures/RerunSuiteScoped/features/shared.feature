Feature: Shared by two suites

  Scenario: one that only fails in one suite
    Given a step that only fails in the "failing" suite

  Scenario: one that always passes
    Given a step that passes
