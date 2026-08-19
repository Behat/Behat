Feature: Tag expressions
  In order to run only needed features
  As a Behat user
  I need Behat to support Cucumber tag expressions

  Background:
    Given I initialise the working directory from the "TagFilters" fixtures folder
    And I provide the following options for all behat invocations:
      | option            | value              |
      | --no-colors       |                    |
      | --format-settings | '{"paths": false}' |

  Scenario: And expression
    When I run "behat --tag-expression '@slow and @fast'"
    Then it should pass
    And the output should contain:
      """
      @slow
      Feature: Feature N1

        Background:
          Given Some slow step N11

        @fast
        Scenario:
          Given Some fast step N14
      """
    And the output should contain:
      """
      Feature: Feature N2

        Background:
          Given Some normal step N21

        @slow @fast
        Scenario:
          Given Some slow step N22
          And Some fast step N23
      """
    And the output should contain:
      """
      2 scenarios (2 passed)
      5 steps (5 passed)
      """

  Scenario: Not expression
    When I run "behat --tag-expression 'not @slow'"
    Then it should pass
    And the output should contain:
      """
      Feature: Feature N2

        Background:
          Given Some normal step N21

        @fast
        Scenario:
          Given Some fast step N24
          And Some fast step N25
      """
    And the output should contain:
      """
      Feature: Feature N3

        Background:
          Given Some normal step N21

        @normal
        Scenario:
          Given Some normal step N38

        @fast
        Scenario Outline:
          Given Some fast step N<num>

          Examples:
            | num |
            | 33  |
            | 34  |

        @normal @fast
        Scenario Outline:
          Given Some normal step N<num>
          And Some fast step N37

          Examples:
            | num |
            | 35  |
            | 36  |
      """
    And the output should contain:
      """
      Feature: Feature N4

        @normal
        Scenario:
          Given Some normal step N41
          And Some fast step N42

        @fast
        Scenario:
          Given Some slow step N43
      """
    And the output should contain:
      """
      8 scenarios (8 passed)
      18 steps (18 passed)
      """

  Scenario: Outlines are filtered by the expression
    When I run "behat --tag-expression '@normal and @fast'"
    Then it should pass
    And the output should contain:
      """
      Feature: Feature N3

        Background:
          Given Some normal step N21

        @normal @fast
        Scenario Outline:
          Given Some normal step N<num>
          And Some fast step N37

          Examples:
            | num |
            | 35  |
            | 36  |
      """
    And the output should contain:
      """
      2 scenarios (2 passed)
      6 steps (6 passed)
      """

  Scenario: Outline examples are filtered by the expression
    When I initialise the working directory from the "TagExpressionExamples" fixtures folder
    And I run "behat features/outline_examples.feature --tag-expression '@quick'"
    Then it should pass
    And the output should contain:
      """
      Feature: Feature N5

        Scenario Outline:
          Given Some normal step N<num>

          Examples:
            | num |
            | 51  |
            | 52  |
      """
    And the output should contain:
      """
      2 scenarios (2 passed)
      2 steps (2 passed)
      """

  Scenario: Tag expression suite filter in the config file
    When I run "behat --config=behat-with-tag-expression-filter.php"
    Then it should pass
    And the output should contain:
      """
      Feature: Feature N2

        Background:
          Given Some normal step N21

        @fast
        Scenario:
          Given Some fast step N24
          And Some fast step N25
      """
    And the output should contain:
      """
      Feature: Feature N3

        Background:
          Given Some normal step N21

        @fast
        Scenario Outline:
          Given Some fast step N<num>

          Examples:
            | num |
            | 33  |
            | 34  |

        @normal @fast
        Scenario Outline:
          Given Some normal step N<num>
          And Some fast step N37

          Examples:
            | num |
            | 35  |
            | 36  |
      """
    And the output should contain:
      """
      Feature: Feature N4

        @fast
        Scenario:
          Given Some slow step N43
      """
    And the output should contain:
      """
      6 scenarios (6 passed)
      14 steps (14 passed)
      """

  Scenario: Invalid tag expression
    When I run "behat --tag-expression '@a and ('"
    Then it should fail
    And the output should contain:
      """
      Tag expression "@a and (" could not be parsed because of syntax error: Unmatched (.
      """
