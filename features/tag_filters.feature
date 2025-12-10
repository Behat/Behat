Feature: Tags
  In order to run only needed features
  As a Behat user
  I need Behat to support features & scenario/outline tags

  Background:
    Given I initialise the working directory from the "TagFilters" fixtures folder
    And I provide the following options for all behat invocations:
      | option            | value              |
      | --no-colors       |                    |
      | --format-settings | '{"paths": false}' |


  Scenario: Single tag
    When I run "behat --tags '@slow'"
    Then it should pass
    And the output should contain:
      """
      @slow
      Feature: Feature N1

        Background:
          Given Some slow step N11

        Scenario:
          Given Some slow step N12
          And Some normal step N13

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
      Feature: Feature N3

        Background:
          Given Some normal step N21

        @slow
        Scenario Outline:
          Given Some slow step N<num>

          Examples:
            | num |
            | 31  |
            | 32  |
      """
    And the output should contain:
      """
      5 scenarios (5 passed)
      12 steps (12 passed)
      """

  Scenario: Or tags
    When I run "behat --tags '@slow,@normal'"
    Then it should pass
    And the output should contain:
      """
      @slow
      Feature: Feature N1

        Background:
          Given Some slow step N11

        Scenario:
          Given Some slow step N12
          And Some normal step N13

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
      Feature: Feature N3

        Background:
          Given Some normal step N21

        @slow
        Scenario Outline:
          Given Some slow step N<num>

          Examples:
            | num |
            | 31  |
            | 32  |

        @normal
        Scenario:
          Given Some normal step N38

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
      """
    And the output should contain:
      """
      9 scenarios (9 passed)
      22 steps (22 passed)
      """

  Scenario: Overriding behat.yml filters with CLI options
    When I run "behat --config=behat-with-filter.php --tags '@slow'"
    Then it should pass
    And the output should contain:
      """
      @slow
      Feature: Feature N1

        Background:
          Given Some slow step N11

        Scenario:
          Given Some slow step N12
          And Some normal step N13

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
      Feature: Feature N3

        Background:
          Given Some normal step N21

        @slow
        Scenario Outline:
          Given Some slow step N<num>

          Examples:
            | num |
            | 31  |
            | 32  |
      """
    And the output should contain:
      """
      5 scenarios (5 passed)
      12 steps (12 passed)
      """
