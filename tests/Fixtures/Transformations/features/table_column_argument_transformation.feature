Feature: Table column argument transformation
  As a feature developer
  I want to be able to transform table columns

  Scenario:
    Given I am a user with this age:
      | user     | age |
      | ever.zet | 33  |
    Then Username must be "ever.zet"
    And Age must be 33

  Scenario:
    Given I have two users and I add their ages
      | user     | other user |
      | ever.zet | roland     |
    Then total age must be 40

  Scenario:
    Given I am a user with this hex age:
      | user     | hex age   |
      | ever.zet | 0x1A      |
    Then Username must be "ever.zet"
    And Age must be 26

  Scenario:
    Given I am a Russian user with this age:
      | логин    | age |
      | ever.zet | 33  |
    Then Username must be "ever.zet"
    And Age must be 33
