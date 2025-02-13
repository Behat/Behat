Feature: Whole table argument transformation
  As a feature developer
  I want to be able to transform whole tables

  Scenario:
    Given data:
      | username | age |
      | ever.zet | 22  |
    Then the "username" should be "ever.zet"
    And the "age" should be 22
