Feature: Rules that have tagging

  @maths
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


  @offset
  Rule: Calculator can be forced to add one to every result

    Background:
      Given the calculator has a fixed offset of 1

    Scenario: Adding numbers
      When I add 2 + 2
      Then the result should be 5

    @smoketest
    Scenario: Dividing numbers
      When I divide <dividend> by <divisor>
      Then the result should be <answer>

      Examples: That actually work
        | dividend | divisor | answer |
        | 9        | 3       | 4      |

      @invalid
      Examples: That are made-up to fail
        | dividend | divisor | answer |
        | 6        | 2       | 3      | # Oops, incorrect (no offset)
