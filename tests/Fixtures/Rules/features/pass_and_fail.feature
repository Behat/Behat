Feature: Rules that pass and fail

  Background:
    Given some setup has happened

  Rule: Calculator follows rules of maths

    Scenario: Adding numbers
      When I add 2 + 2
      Then the result should be 4

    Scenario: Dividing numbers
      When I divide <dividend> by <divisor>
      Then the result should be <answer>

      Examples:
        | dividend | divisor | answer |
        | 6        | 2       | 3      |

  Rule: Calculator can be forced to add one to every result

    Background:
      Given the calculator has a fixed offset of 1

    Scenario: Adding numbers
      When I add 2 + 2
      Then the result should be 5

    Scenario: Dividing numbers
      When I divide <dividend> by <divisor>
      Then the result should be <answer>

      Examples:
        | dividend | divisor | answer |
        | 6        | 2       | 3      | # Oops, incorrect (no offset)
        | 9        | 3       | 4      |
