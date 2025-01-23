Feature: By type and by name object transformation
  As a feature developer
  I want to be able to transform arguments into Users by type and by name

  Scenario:
    Given I am "everzet"
    And she is "lunivore"
    Then I should be a user named "everzet"
    And she should be an admin named "admin: lunivore"
