Feature: Tables
  Scenario:
    Given a table:
      | item1 | item2 | item3 |
      | super | mega  | extra |
      | hyper | mini  | XXL   |
    Then it must be equals to table 1
