Feature: multiple transformations in one function
  As a feature developer
  I want to be able to transform step arguments with a function that has multiple transformations

  Scenario:
    Given I am everzet
    Then Username must be "everzet"
    And Age must be 20

  Scenario:
    Given I am "antono - 29" user
    Then Username must be "antono"
    And Age must be 29
