Feature: Tables
  Scenario Outline:
    Given a table:
      | item1   | item2   | item3   |
      | <word1> | <word3> | extra   |
      | hyper   | mini    | <word2> |
    Then it must be equals to table 1

    Examples:
      | word1 | word2 | word3 |
      | super | XXL   | mega  |
