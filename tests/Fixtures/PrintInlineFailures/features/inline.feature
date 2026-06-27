Feature: Inline failures

  Scenario: Passing then failing
    Given a passing step
    And a failing step

  Scenario: Pending
    Given a pending step

  Scenario: Undefined
    Given an undefined step
