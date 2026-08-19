Feature: Data tables
  Scenario:
    Given a data table:
      | item1 | item2 | item3 |
      | super | mega  | extra |
      | hyper | mini  | XXL   |
    Then the data table must be equals to table 1

  Scenario:
    Given a table that could be wrapped or not:
      | item1 | item2 | item3 |
      | super | mega  | extra |
    Then the argument must be a raw TableNode
