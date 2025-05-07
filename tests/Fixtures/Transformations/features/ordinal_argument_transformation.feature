Feature: Ordinal argument transformation
  As a feature developer
  I want to be able to transform ordinal arguments with and without quotes

  Scenario:
    Given I pick the 1st thing
    Then the index should be "1"

  Scenario:
    Given I pick the "1st" thing
    Then the index should be "1"

  Scenario:
    Given I pick the 27th thing
    Then the index should be "27"

  Scenario:
    Given I pick the 5 thing
    Then the index should be "5"
