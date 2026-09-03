Feature: Data tables
  Scenario:
    Given a data table:
      | item1 | item2 | item3 |
      | super | mega  | extra |
      | hyper | mini  | XXL   |
    Then the data table must be equals to table 1

  Scenario: A step with no declared types still receives the raw Gherkin node
    Given an untyped step that takes "hello" and these values:
      | item1 | item2 | item3 |
      | super | mega  | extra |
    Then the argument must be a raw TableNode
    And the other argument must be "hello"
