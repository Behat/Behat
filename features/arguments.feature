Feature: Step Arguments
  In order to write extended steps
  As a feature writer
  I need an ability to specify Table & PyString arguments to steps

  Background:
    Given I initialise the working directory from the Arguments fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value    |
      | --no-colors |          |
      | --format    | progress |

  Scenario: PyStrings
    When I run behat with the following additional options:
      | option                    | value |
      | features/pystring.feature |       |
    Then it should pass with:
      """
      ..

      1 scenario (1 passed)
      2 steps (2 passed)
      """

  Scenario: PyString tokens
    When I run behat with the following additional options:
      | option                           | value |
      | features/pystring_tokens.feature |       |
    Then it should pass with:
      """
      ..

      1 scenario (1 passed)
      2 steps (2 passed)
      """

  Scenario: Table tokens
    When I run behat with the following additional options:
      | option                        | value |
      | features/table_tokens.feature |       |
    Then it should pass with:
      """
      ..

      1 scenario (1 passed)
      2 steps (2 passed)
      """

  Scenario: Table
    When I run behat with the following additional options:
      | option                 | value |
      | features/table.feature |       |
    Then it should pass with:
      """
      ..

      1 scenario (1 passed)
      2 steps (2 passed)
      """

  Scenario: given TableNode argument that is not defined in context
    When I run behat with the following additional options:
      | option                                      | value |
      | features/unexpected-table-exception.feature |       |
    Then it should fail with:
      """
        You have passed a TableNode or PystringNode, but it was not used by FeatureContext::aStepWithNoArgument.
        This is probably an error in your step implementation or in %%WORKING_DIR%%features%%DS%%unexpected-table-exception.feature:3
      """

  Scenario: given TableNode that could match an un-typed step argument
    When I run behat with the following additional options:
      | option                                   | value |
      | features/step-with-untyped-table.feature |       |
    Then it should pass with:
      """
      .

      1 scenario (1 passed)
      1 step (1 passed)
      """

  Scenario: given PyStringNode argument that is not defined in context
    When I run behat with the following additional options:
      | option                                         | value |
      | features/unexpected-pystring-exception.feature |       |
    Then it should fail with:
      """
        You have passed a TableNode or PystringNode, but it was not used by FeatureContext::aStepWithNoArgument.
        This is probably an error in your step implementation or in %%WORKING_DIR%%features%%DS%%unexpected-pystring-exception.feature:3
      """

  Scenario: given PyString that could match an un-typed step argument
    When I run behat with the following additional options:
      | option                                      | value |
      | features/step-with-untyped-pystring.feature |       |
    Then it should pass with:
      """
      .

      1 scenario (1 passed)
      1 step (1 passed)
      """

  Scenario: Named arguments
    When I run behat with the following additional options:
      | option                      | value |
      | features/named_args.feature |       |
    Then it should pass with:
      """
      .

      1 scenario (1 passed)
      1 step (1 passed)
      """
