Feature: Step Arguments Transformations with Attributes
  In order to follow DRY
  As a feature writer
  I need to use transformation functions using PHP attributes

  Background:
    Given I initialise the working directory from the "Transformations" fixtures folder
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

  Scenario: Table Arguments Transformations
    When I run behat with the following additional options:
      | option         | value                         |
      | --suite        | table_argument_transformation |
    Then it should pass with:
      """
      ............

      4 scenarios (4 passed)
      12 steps (12 passed)
      """

  Scenario: Row Table Arguments Transformations
    When I run behat with the following additional options:
      | option         | value                             |
      | --suite        | row_table_argument_transformation |
    Then it should pass with:
      """
      ............

      4 scenarios (4 passed)
      12 steps (12 passed)
      """

  Scenario: Table Row Arguments Transformations
    When I run behat with the following additional options:
      | option         | value                             |
      | --suite        | table_row_argument_transformation |
    Then it should pass with:
      """
      ......

      3 scenarios (3 passed)
      6 steps (6 passed)
      """

  Scenario: Whole table transformation
    When I run behat with the following additional options:
      | option         | value                               |
      | --suite        | whole_table_argument_transformation |
    Then it should pass with:
      """
      ...

      1 scenario (1 passed)
      3 steps (3 passed)
      """

  Scenario: Named Arguments Transformations
    When I run behat with the following additional options:
      | option         | value                         |
      | --suite        | named_argument_transformation |
    Then it should pass with:
      """
      ....

      2 scenarios (2 passed)
      4 steps (4 passed)
      """

  Scenario: Transforming different types
    When I run behat with the following additional options:
      | option         | value                     |
      | --suite        | transform_different_types |
    Then it should pass with:
      """
      ........

      4 scenarios (4 passed)
      8 steps (8 passed)
      """

  Scenario: By-type object transformations
    When I run behat with the following additional options:
      | option         | value                         |
      | --suite        | by_type_object_transformation |
    Then it should pass with:
      """
      ....

      1 scenario (1 passed)
      4 steps (4 passed)
      """

  Scenario: By-type and by-name object transformations
    When I run behat with the following additional options:
      | option         | value                                     |
      | --suite        | by_type_and_by_name_object_transformation |
    Then it should pass with:
      """
      ....

      1 scenario (1 passed)
      4 steps (4 passed)
      """

  Scenario: Unicode Named Arguments Transformations
    When I run behat with the following additional options:
      | option         | value                                 |
      | --suite        | unicode_named_argument_transformation |
    Then it should pass with:
      """
      ....

      2 scenarios (2 passed)
      4 steps (4 passed)
      """

  Scenario: Ordinal Arguments without quotes Transformations
    When I run behat with the following additional options:
      | option         | value                           |
      | --suite        | ordinal_argument_transformation |
    Then it should pass with:
      """
      ........

      4 scenarios (4 passed)
      8 steps (8 passed)
      """

  Scenario: By-type transformations don't trigger from union types
    When I run behat with the following additional options:
      | option         | value                        |
      | --suite        | by_type_union_transformation |
    Then it should fail with:
      """
      must be of type User, string given
      """

  Scenario: Return type transformations don't cause issues with scalar type hints (regression)
    When I run behat with the following additional options:
      | option         | value                      |
      | --suite        | scalar_type_transformation |
    Then it should pass
