Feature:
  Scenario:
    Then I must have 50
  Scenario:
    Given I have entered 12
    Then I must have 12

  @thirty
  Scenario:
    Given I must have 30
    When I have entered 23
    Then I must have 23
  @100 @thirty
  Scenario:
    Given I must have 30
    When I have entered 1
    Then I must have 100

  @filtered
  Scenario:
    Then I must have a scenario filter value of "filtered"

  @~filtered
  Scenario:
    Then I must have a scenario filter value of "~filtered"
