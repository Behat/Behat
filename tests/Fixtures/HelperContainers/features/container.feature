Feature:
  Scenario:
    Given service has no state
    When service gets a state of 1 in first context
    Then service should have a state of 1 in second context

  Scenario:
    Given service has no state
    When service gets a state of 33 in first context
    Then service should have a state of 33 in second context
