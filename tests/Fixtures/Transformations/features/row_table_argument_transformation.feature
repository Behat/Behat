Feature: Row table argument transformation
  As a feature developer
  I want to be able to transform table arguments where the data is in rows into Users

  Scenario:
    Given I am user:
      | username | ever.zet |
      | age      | 22       |
    Then Username must be "ever.zet"
    And Age must be 22

  Scenario:
    Given I am user:
      | username | vasiljev |
      | age      | 30       |
    Then Username must be "vasiljev"
    And Age must be 30

  Scenario:
    Given I am user:
      | --username | rajesh |
      | age        | 35     |
    Then Username must be "rajesh"
    And Age must be 35

  Scenario:
    Given I am user:
      | логин   | vasiljev |
      | возраст | 30       |
    Then Username must be "vasiljev"
    And Age must be 30
