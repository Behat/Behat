Feature: Pretty Formatter
  In order to debug features
  As a feature writer
  I need to have pretty formatter

  Background:
    Given I initialise the working directory from the "PrettyFormat" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value |
      | --no-colors |       |

  Scenario: Complex
    When I run "behat features/World.feature"
    Then it should fail with:
      """
      Feature: World consistency
        In order to maintain stable behaviors
        As a features developer
        I want, that "World" flushes between scenarios

        Background:               # features/World.feature:6
          Given I have entered 10 # FeatureContext::iHaveEntered()

        Scenario: Undefined   # features/World.feature:9
          Then I must have 10 # FeatureContext::iMustHave()
          And Something new
          Then I must have 10 # FeatureContext::iMustHave()

        Scenario: Pending            # features/World.feature:14
          Then I must have 10        # FeatureContext::iMustHave()
          And Something not done yet # FeatureContext::somethingNotDoneYet()
            TODO: write pending definition
          Then I must have 10        # FeatureContext::iMustHave()

        Scenario: Failed      # features/World.feature:19
          When I add 4        # FeatureContext::iAdd()
          Then I must have 13 # FeatureContext::iMustHave()
            Failed asserting that 14 matches expected '13'.

        Scenario Outline: Passed & Failed # features/World.feature:23
          Given I must have 10            # FeatureContext::iMustHave()
          When I add <value>              # FeatureContext::iAdd()
          Then I must have <result>       # FeatureContext::iMustHave()

          Examples:
            | value | result |
            | 5     | 16     |
              Failed step: Then I must have 16
              Failed asserting that 15 matches expected '16'.
            | 10    | 20     |
            | 23    | 32     |
              Failed step: Then I must have 32
              Failed asserting that 33 matches expected '32'.

      --- Failed scenarios:

          features/World.feature:19 (on line 21)
          features/World.feature:30 (on line 26)
          features/World.feature:32 (on line 26)

      6 scenarios (1 passed, 3 failed, 1 undefined, 1 pending)
      23 steps (16 passed, 3 failed, 1 undefined, 1 pending, 2 skipped)

      --- Use --snippets-for CLI option to generate snippets for following default suite steps:

          And Something new
      """

  Scenario: Multiline titles
    When I run "behat --profile=multiline features/WorldMultiline.feature"
    Then it should pass with:
      """
      Feature: World consistency
        In order to maintain stable behaviors
        As a features developer
        I want, that "World" flushes between scenarios

        Background:               # features/WorldMultiline.feature:6
          Given I have entered 10 # FeatureContextMultiline::iHaveEntered()

        Scenario: Adding some interesting # features/WorldMultiline.feature:9
                  value
          Then I must have 10             # FeatureContextMultiline::iMustHave()
          And I add the value 6           # FeatureContextMultiline::iAddOrSubtract()
          Then I must have 16             # FeatureContextMultiline::iMustHave()

        Scenario: Subtracting        # features/WorldMultiline.feature:15
                  some
                  value
          Then I must have 10        # FeatureContextMultiline::iMustHave()
          And I subtract the value 6 # FeatureContextMultiline::iAddOrSubtract()
          Then I must have 4         # FeatureContextMultiline::iMustHave()

      2 scenarios (2 passed)
      8 steps (8 passed)
      """

    Scenario: Don't print undefined exceptions in outline
      When I run "behat --profile=ls features/ls.feature --no-snippets"
      Then it should pass with:
        """
        Feature: ls
          In order to see the directory structure
          As a UNIX user
          I need to be able to list the current directory's contents

          Background:                       # features/ls.feature:6
            Given I have a file named "foo"

          Scenario: List 2 files in a directory # features/ls.feature:9
            Given I have a file named "bar"
            When I run "ls"
            Then I should see "bar" in output
            And I should see "foo" in output

          Scenario: List 1 file and 1 dir        # features/ls.feature:15
            Given I have a directory named "dir"
            When I run "ls"
            Then I should see "dir" in output
            And I should see "foo" in output

          Scenario Outline:                        # features/ls.feature:21
            Given I have a <object> named "<name>"
            When I run "ls"
            Then I should see "<name>" in output
            And I should see "foo" in output

            Examples:
              | object    | name |
              | file      | bar  |
              | directory | dir  |

        4 scenarios (4 undefined)
        20 steps (20 undefined)
        """

  Scenario: Multiline titles
    When I run "behat --profile=empty features/WorldMultilineBackgroundScenario.feature --no-snippets"
    Then it should pass with:
      """
      Feature: World consistency
        In order to maintain stable behaviors
        As a features developer
        I want, that "World" flushes between scenarios

        Background: Some background # features/WorldMultilineBackgroundScenario.feature:6
          title
            with
          multiple lines
          Given I have entered 10

        Scenario: Undefined   # features/WorldMultilineBackgroundScenario.feature:13
                  scenario or
                  whatever
          Then I must have 10
          And Something new
          Then I must have 10

        Scenario Outline: Passed & Failed # features/WorldMultilineBackgroundScenario.feature:20
          steps and other interesting stuff
          he-he-he
          Given I must have 10
          When I add <value>
          Then I must have <result>

          Examples:
            | value | result |
            | 5     | 16     |
            | 10    | 20     |
            | 23    | 32     |

      4 scenarios (4 undefined)
      16 steps (16 undefined)
      """

  Scenario: Background with failing step and 2 scenarios
    When I run "behat --profile=background_failing features/test.feature --no-snippets"
    Then it should pass with:
      """
      Feature: Customer can see the cost of their purchase in basket
        In order to see the cost of my purchase
        As a customer
        I need to see the totals of my basket

        Background:                                             # features/test.feature:6
          Given there are the following products in the catalog # FeatureContextBackgroundFailing::anything()
            | name     | price |
            | trousers | 12    |
            TODO: write pending definition

        Scenario: £12 delivery £3                        # features/test.feature:11
          Given I have an empty basket                   # FeatureContextBackgroundFailing::anything()
          When I add the product "trousers" to my basket # FeatureContextBackgroundFailing::anything()

        Scenario: £12 delivery £3                        # features/test.feature:15
          Given there are the following products in the catalog # FeatureContextBackgroundFailing::anything()
            | name     | price |
            | trousers | 12    |
            TODO: write pending definition
          Given I have an empty basket                   # FeatureContextBackgroundFailing::anything()
          When I add the product "trousers" to my basket # FeatureContextBackgroundFailing::anything()

      2 scenarios (2 pending)
      6 steps (2 pending, 4 skipped)
      """

  Scenario: Multiple examples tables
    When I run "behat --profile=multiple_examples features/testMultipleExamples.feature --no-snippets"
    # Note: The structure / descriptions of the separate tables are lost in the output, but the
    # examples all execute as expected.
    Then it should fail with:
      """
      Feature: Behat can run scenarios with multiple examples tables
        In order to make the purpose of my examples clear
        As a feature writer
        I need to group examples into separate tables

        Scenario Outline: Grouped examples # features/testMultipleExamples.feature:6
          When I input <name>              # FeatureContextMultipleExamples::input()
          Then I should see "<result>"     # FeatureContextMultipleExamples::assertSee()

          Examples:
            | name  | result   |
            | Bob   | Hi Bob   |
            | Jenny | Hi Jenny |
              Failed step: Then I should see "Hi Jenny"
              Failed - got: Hi Bob (InvalidArgumentException)
            | 123456 | '123456' doesn't look like a name? |
            | Brian | Sorry Brian, you're banned |
              Failed step: Then I should see "Sorry Brian, you're banned"
              Failed - got: Hi Bob (InvalidArgumentException)

      --- Failed scenarios:

          features/testMultipleExamples.feature:13 (on line 8)
          features/testMultipleExamples.feature:18 (on line 8)

      4 scenarios (2 passed, 2 failed)
      8 steps (6 passed, 2 failed)
      """
