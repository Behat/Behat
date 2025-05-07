Feature: Transform different types
  As a feature developer
  I want to be able to transform strings into different types

  Scenario Outline: Converting different types
    Given I have the value "<value>"
    Then it should be of type "<type>"

    Examples:
      | value          | type     |
      | "soeuhtou"     | string   |
      | 34             | integer  |
      | null           | NULL     |
      | 2 workdays ago | DateTime |
