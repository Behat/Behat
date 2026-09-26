Feature: Run tagged hooks based on rule tags

  @binary
  Scenario: Tagged hook works on the Scenario
    When I add 3 + 3
    Then the result should be 6

  @binary
  Rule: Calculator follows rules of maths

    @smoketest
    Scenario: Adding numbers
      When I add 3 + 3
      Then the result should be 6

    Scenario: Dividing numbers
      When I divide <dividend> by <divisor>
      Then the result should be <answer>

      Examples:
        | dividend | divisor | answer |
        | 6        | 2       | 3      |
        | 1        | 1       | 1      |


  Rule: Works if the hook didn't run

    Scenario: Adding numbers
      When I add 3 + 3
      Then the result should be 6
