Feature: Step Arguments Transformations with Attributes
  In order to follow DRY
  As a feature writer
  I need to use transformation functions using PHP attributes

  Background:
    Given I set the working directory to the "Transformations" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value      |
      | --no-colors |            |
      | --format    | progress   |
      | --profile   | attributes |

  Scenario: Simple Argument Transformations
    When I run behat with the following additional options:
      | option         | value                               |
      | --suite        | simple_step_argument_transformation |
    Then it should pass with:
      """
      .......

      2 scenarios (2 passed)
      7 steps (7 passed)
      """

  Scenario: Transformation without parameters
    When I run behat with the following additional options:
      | option         | value                                           |
      | --suite        | step_argument_transformation_without_parameters |
    Then it should pass with:
      """
      ..

      1 scenario (1 passed)
      2 steps (2 passed)
      """

  Scenario: Multiple Transformations in one function
    When I run behat with the following additional options:
      | option         | value                                    |
      | --suite        | multiple_transformations_in_one_function |
    Then it should pass with:
      """
      ......

      2 scenarios (2 passed)
      6 steps (6 passed)
      """

  Scenario: Table Column Arguments Transformations
    When I run behat with the following additional options:
      | option         | value                                |
      | --suite        | table_column_argument_transformation |
    Then it should pass with:
      """
      ...........

      4 scenarios (4 passed)
      11 steps (11 passed)
      """

