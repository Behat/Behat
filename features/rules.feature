@gherkin-mode:has-explicit
Feature: Support Gherkin Rules
  In order to organise my feature files into distinct groups of examples
  As a user practising Example Mapping
  I need Behat to support the Rule keyword

  Background:
    Given I initialise the working directory from the "Rules" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value |
      | --no-colors |       |

  Scenario: Fails to parse in legacy gherkin mode
    When I run behat with the following additional options:
      | option    | value          |
      | --profile | legacy-gherkin |
    Then it should fail with:
      """
      Expected Step, but got text: "  Rule: Calculator follows rules of maths"
      """


  Rule: Tests run inside rules

    Scenario: Run tests
      When I run "behat features/pass_and_fail.feature"
      Then it should fail with:
        """
        .............F....

        --- Failed steps:

        001 Example: | 6        | 2       | 3      | # features/pass_and_fail.feature:35
              Then the result should be 3            # features/pass_and_fail.feature:31
                Failed asserting that 3 is identical to 4. (Exception)

        5 scenarios (4 passed, 1 failed)
        18 steps (17 passed, 1 failed)
        """

  Rule: Formatters can render usable output

    Scenario: Pretty formatter approximates the original gherkin (drops `Rule` concept)
      When I run behat with the following additional options:
        | option                         | value  |
        | --format                       | pretty |
        | features/pass_and_fail.feature |        |
      Then it should fail with:
        """
        Feature: Rules that pass and fail

          Background:                     # features/pass_and_fail.feature:3
            Given some setup has happened # FeatureContext::someSetupHasHappened()

          Scenario: Adding numbers      # features/pass_and_fail.feature:8
            When I add 2 + 2            # FeatureContext::iAdd()
            Then the result should be 4 # FeatureContext::theResultShouldBe()

          Scenario: Dividing numbers              # features/pass_and_fail.feature:12
            When I divide <dividend> by <divisor> # FeatureContext::iDivideBy()
            Then the result should be <answer>    # FeatureContext::theResultShouldBe()

            Examples:
              | dividend | divisor | answer |
              | 6        | 2       | 3      |

          Scenario: Adding numbers                       # features/pass_and_fail.feature:25
            Given the calculator has a fixed offset of 1 # FeatureContext::theCalculatorHasAFixedOffsetOf()
            When I add 2 + 2                             # FeatureContext::iAdd()
            Then the result should be 5                  # FeatureContext::theResultShouldBe()

          Scenario: Dividing numbers                     # features/pass_and_fail.feature:29
            Given the calculator has a fixed offset of 1 # FeatureContext::theCalculatorHasAFixedOffsetOf()
            When I divide <dividend> by <divisor>        # FeatureContext::iDivideBy()
            Then the result should be <answer>           # FeatureContext::theResultShouldBe()

            Examples:
              | dividend | divisor | answer |
              | 6        | 2       | 3      |
                Failed step: Then the result should be 3
                Failed asserting that 3 is identical to 4. (Exception)
              | 9        | 3       | 4      |

        --- Failed scenarios:

            features/pass_and_fail.feature:35 (on line 31)
        """

  Rule: Tag filters apply inside rules

    Background:
      When I provide the following options for all behat invocations:
        | option   | value  |
        | --format | pretty |

    Scenario: Filter at Rule and Scenario level
      When I run behat with the following additional options:
        | option | value                  |
        | --tags | '@maths && @smoketest' |
      # @todo: Gherkin should merge the `@maths` tag into hoisted nodes for use by other tools that don't know rules (including us)
      Then it should pass with:
          """
          Feature: Rules that have tagging

            @smoketest
            Scenario: Adding numbers      # features/tagged.feature:7
              When I add 3 + 3            # FeatureContext::iAdd()
              Then the result should be 6 # FeatureContext::theResultShouldBe()

          1 scenario (1 passed)
          2 steps (2 passed)
          """

    Scenario: Filter tables inside rules
      When I run behat with the following additional options:
        | option | value                  |
        | --tags | '@offset && ~@invalid' |
      Then it should pass with:
          """
          Feature: Rules that have tagging

            Scenario: Adding numbers                       # features/tagged.feature:26
              Given the calculator has a fixed offset of 1 # FeatureContext::theCalculatorHasAFixedOffsetOf()
              When I add 2 + 2                             # FeatureContext::iAdd()
              Then the result should be 5                  # FeatureContext::theResultShouldBe()

            @smoketest
            Scenario: Dividing numbers                     # features/tagged.feature:31
              Given the calculator has a fixed offset of 1 # FeatureContext::theCalculatorHasAFixedOffsetOf()
              When I divide <dividend> by <divisor>        # FeatureContext::iDivideBy()
              Then the result should be <answer>           # FeatureContext::theResultShouldBe()

              Examples:
                | dividend | divisor | answer |
                | 9        | 3       | 4      |

          2 scenarios (2 passed)
          6 steps (6 passed)
          """
