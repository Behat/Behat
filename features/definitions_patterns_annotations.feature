Feature: Step Definition Pattern Annotations
  In order to fix my mistakes easily
  As a step definitions developer
  I need to be able to use complex and weird patterns using annotations

  Background:
    Given I initialise the working directory from the "DefinitionsPatterns" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value                 |
      | --no-colors |                       |
      | --config    | behat-annotations.php |

  Scenario: Pattern with token at the start of the step
    When I run behat with the following additional options:
      | option                       | value       |
      | --format                     | progress    |
      | --profile                    | token_start |
      | features/token_start.feature |             |
    Then it should pass with:
      """
      ..

      1 scenario (1 passed)
      2 steps (2 passed)
      """

  Scenario: Pattern with decimal point
    When I run behat with the following additional options:
      | option                          | value         |
      | --format                        | pretty        |
      | --profile                       | decimal_point |
      | features/decimal_point.feature  |               |
    Then it should pass with:
      """
      Feature: Step Pattern

        Scenario:                         # features/decimal_point.feature:2
          Then 5 should have value of £10 # DecimalPointAnnotations::shouldHaveValueOf()
            │ 10
          And 7 should have value of £7.2 # DecimalPointAnnotations::shouldHaveValueOf()
            │ 7.2

      1 scenario (1 passed)
      2 steps (2 passed)
      """

  Scenario: Pattern with string including point
    When I run behat with the following additional options:
      | option                              | value              |
      | --format                            | pretty             |
      | --profile                           | string_with_point  |
      | features/string_with_point.feature  |                    |
    Then it should pass with:
      """
      Feature: Step Pattern

        Scenario:                               # features/string_with_point.feature:2
          Then 5 should have value of two.three # StringWithPointAnnotations::shouldHaveValueOf()
            │ two + three
          And 7 should have value of three.4    # StringWithPointAnnotations::shouldHaveValueOf()
            │ three + 4
          And 7 should have value of 3.four     # StringWithPointAnnotations::shouldHaveValueOf()
            │ 3 + four

      1 scenario (1 passed)
      3 steps (3 passed)
      """

  Scenario: Pattern with broken regex
    When I run behat with the following additional options:
      | option                         | value        |
      | --format                       | progress     |
      | --profile                      | broken_regex |
      | features/broken_regex.feature  |              |
    Then it should fail with:
      """
      In RegexPatternPolicy.php line 69:
      
        The regex `/I am (foo/` is invalid: preg_match(): Compilation failed: missing closing parenthesis at offset 9
      """

  Scenario: Custom regex
    When I run behat with the following additional options:
      | option                         | value        |
      | --format                       | progress     |
      | --profile                      | custom_regex |
      | features/custom_regex.feature  |              |
    Then it should pass with:
      """
      ..

      1 scenario (1 passed)
      2 steps (2 passed)
      """

  Scenario: Definition parameter with decimal number following string
    When I run behat with the following additional options:
      | option                           | value          |
      | --format                         | progress       |
      | --profile                        | decimal_number |
      | features/decimal_number.feature  |                |
    Then it should pass with:
      """
      .

      1 scenario (1 passed)
      1 step (1 passed)
      """

  Scenario: Empty parameter value
    When I run behat with the following additional options:
      | option                             | value           |
      | --format                           | progress        |
      | --profile                          | empty_parameter |
      | features/empty_parameter.feature   |                 |
    Then it should pass with:
      """
      .

      1 scenario (1 passed)
      1 step (1 passed)
      """

  Scenario: UNIX path as parameter
    When I run behat with the following additional options:
      | option                       | value     |
      | --format                     | progress  |
      | --profile                    | unix_path |
      | features/unix_path.feature   |           |
    Then it should pass with:
      """
      .

      1 scenario (1 passed)
      1 step (1 passed)
      """

  Scenario: Negative number parameters without quotes
    When I run behat with the following additional options:
      | option                             | value           |
      | --format                           | progress        |
      | --profile                          | negative_number |
      | features/negative_number.feature   |                 |
    Then it should pass with:
      """
      .

      1 scenario (1 passed)
      1 step (1 passed)
      """
