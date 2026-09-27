Feature: Step with DataTable and DocString

  Scenario: DataTable followed by DocString
    When I build a templated string:
      | name        | bob     |
      | time_of_day | morning |
      """
      Hello {name},
      It is {time_of_day}.
      """
    Then the result should be:
      """
      Hello bob,
      It is morning.
      """

  Scenario: DocString followed by DataTable
    When I build a templated string:
      """
      Hello {name},
      It is {time_of_day}.
      """
      | name        | betty   |
      | time_of_day | evening |
    Then the result should be:
      """
      Hello betty,
      It is evening.
      """

  Scenario: Prove the steps are implemented
    # This will fail, proving that the steps are implemented and not just passing empty
    When I build a templated string:
      """
      Hello {name},
      It is {time_of_day}.
      """
      | name        | Alisha |
      | time_of_day | night  |
    Then the result should be:
      """
      Hello Alisha,
      It is morning.
      """
