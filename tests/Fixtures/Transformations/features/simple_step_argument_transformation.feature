Feature: Simple step argument transformation
  As a feature developer
  I want to be able to transform step arguments into Users

  Scenario:
    Given I am "everzet" user
    Then Username must be "everzet"
    And Age must be 20
    And the boolean no should be transformed to false

  Scenario:
    Given I am "antono - 29" user
    Then Username must be "antono"
    And Age must be 29
