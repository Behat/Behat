Feature: By type object transformation
  As a feature developer
  I want to be able to transform arguments into Users by type

  Scenario:
    Given I am "everzet"
    And he is "sroze"
    Then I should be a user named "everzet"
    And he should be a user named "sroze"
