Feature: Table argument transformation
  As a feature developer
  I want to be able to transform table arguments into Users

  Scenario:
    Given I am user:
      | username | age |
      | ever.zet | 22  |
    Then Username must be "ever.zet"
    And Age must be 22

  Scenario:
    Given I am user:
      | username | age |
      | vasiljev | 30  |
    Then Username must be "vasiljev"
    And Age must be 30

  Scenario:
    Given I am user:
      | %username@ | age# |
      | rajesh     | 35  |
    Then Username must be "rajesh"
    And Age must be 35

  Scenario:
    Given I am user:
      | логин    | возраст |
      | vasiljev | 30      |
    Then Username must be "vasiljev"
    And Age must be 30
