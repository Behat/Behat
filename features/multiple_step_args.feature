Feature: Multiple step arguments
  In order to use multiple formats
  As a tester
  I need to be able to specify multiple output formats to behat

  Background:
    Given I initialise the working directory from the "MultipleStepArgs" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value  |
      | --no-colors |        |
      | --format    | pretty |
      | --snippets-for  | FeatureContext |
      | --snippets-type | regex          |

  @gherkin-mode:has-explicit
  Scenario: Parsed as expected in modern gherkin
    When I run "behat"
    Then it should fail with:
      ```
      Feature: Step with DataTable and DocString

        Scenario: DataTable followed by DocString # features/step_with_datatable_and_docstring.feature:3
          When I build a templated string:        # FeatureContext::iBuildATemplatedString()
            | name        | bob     |
            | time_of_day | morning |
            """
            Hello {name},
            It is {time_of_day}.
            """
          Then the result should be:              # FeatureContext::theResultShouldBe()
            """
            Hello bob,
            It is morning.
            """

        Scenario: DocString followed by DataTable # features/step_with_datatable_and_docstring.feature:17
          When I build a templated string:        # FeatureContext::iBuildATemplatedString()
            """
            Hello {name},
            It is {time_of_day}.
            """
            | name        | betty   |
            | time_of_day | evening |
          Then the result should be:              # FeatureContext::theResultShouldBe()
            """
            Hello betty,
            It is evening.
            """

        Scenario: Prove the steps are implemented # features/step_with_datatable_and_docstring.feature:31
          When I build a templated string:        # FeatureContext::iBuildATemplatedString()
            """
            Hello {name},
            It is {time_of_day}.
            """
            | name        | Alisha |
            | time_of_day | night  |
          Then the result should be:              # FeatureContext::theResultShouldBe()
            """
            Hello Alisha,
            It is morning.
            """
            Failed asserting that 'Hello Alisha,
            It is night.' is identical to 'Hello Alisha,
            It is morning.'. (Exception)

      --- Failed scenarios:

          features/step_with_datatable_and_docstring.feature:31 (on line 40)

      3 scenarios (2 passed, 1 failed)
      6 steps (5 passed, 1 failed)
      ```
