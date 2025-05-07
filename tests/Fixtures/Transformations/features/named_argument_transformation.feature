Feature: Named argument transformation
  As a feature developer
  I want to be able to transform named arguments into Users

  Scenario:
    Given I am "everzet"
    Then Username must be "everzet"

  Scenario:
    Given I am "antono"
    Then Username must be "antono"
