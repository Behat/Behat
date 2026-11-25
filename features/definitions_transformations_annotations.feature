@expects-deprecations
Feature: Step Arguments Transformations Annotations
  In order to follow DRY
  As a feature writer
  I need to use transformation functions using annotations

  Background:
    Given I initialise the working directory from the "Transformations" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value       |
      | --no-colors |             |
      | --format    | progress    |
      | --profile   | annotations |

  Scenario: Simple Arguments Transformations
    When I run behat with the following additional options:
      | option         | value                               |
      | --suite        | simple_step_argument_transformation |
    Then it should pass with:
      """
      .......

      2 scenarios (2 passed)
      7 steps (7 passed)
      """
    And the following deprecations should be reported:
      """
      13 deprecations triggered (1 unique):

        ⚠ Using annotations to define transformations is deprecated and will be removed in Behat 4.0. Use PHP attributes instead. (13x)
          → {BASE_PATH}src/Behat/Behat/Transformation/Context/Annotation/TransformationAnnotationReader.php:XX
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
    And the following deprecations should be reported:
      """
      13 deprecations triggered (1 unique):

        ⚠ Using annotations to define transformations is deprecated and will be removed in Behat 4.0. Use PHP attributes instead. (13x)
          → {BASE_PATH}src/Behat/Behat/Transformation/Context/Annotation/TransformationAnnotationReader.php:XX
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
    And the following deprecations should be reported:
      """
      13 deprecations triggered (1 unique):

        ⚠ Using annotations to define transformations is deprecated and will be removed in Behat 4.0. Use PHP attributes instead. (13x)
          → {BASE_PATH}src/Behat/Behat/Transformation/Context/Annotation/TransformationAnnotationReader.php:XX
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
    And the following deprecations should be reported:
      """
      13 deprecations triggered (1 unique):

        ⚠ Using annotations to define transformations is deprecated and will be removed in Behat 4.0. Use PHP attributes instead. (13x)
          → {BASE_PATH}src/Behat/Behat/Transformation/Context/Annotation/TransformationAnnotationReader.php:XX
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
    And the following deprecations should be reported:
      """
      3 deprecations triggered (2 unique):

        ⚠ Using annotations to define transformations is deprecated and will be removed in Behat 4.0. Use PHP attributes instead.
          → {BASE_PATH}src/Behat/Behat/Transformation/Context/Annotation/TransformationAnnotationReader.php:XX
        ⚠ Using annotations to define steps is deprecated and will be removed in Behat 4.0. Use PHP attributes instead. (2x)
          → {BASE_PATH}src/Behat/Behat/Definition/Context/Annotation/DefinitionAnnotationReader.php:XX
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
    And the following deprecations should be reported:
      """
      13 deprecations triggered (1 unique):

        ⚠ Using annotations to define transformations is deprecated and will be removed in Behat 4.0. Use PHP attributes instead. (13x)
          → {BASE_PATH}src/Behat/Behat/Transformation/Context/Annotation/TransformationAnnotationReader.php:XX
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
    And the following deprecations should be reported:
      """
      6 deprecations triggered (2 unique):

        ⚠ Using annotations to define transformations is deprecated and will be removed in Behat 4.0. Use PHP attributes instead. (4x)
          → {BASE_PATH}src/Behat/Behat/Transformation/Context/Annotation/TransformationAnnotationReader.php:XX
        ⚠ Using annotations to define steps is deprecated and will be removed in Behat 4.0. Use PHP attributes instead. (2x)
          → {BASE_PATH}src/Behat/Behat/Definition/Context/Annotation/DefinitionAnnotationReader.php:XX
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
    And the following deprecations should be reported:
      """
      5 deprecations triggered (2 unique):

        ⚠ Using annotations to define transformations is deprecated and will be removed in Behat 4.0. Use PHP attributes instead.
          → {BASE_PATH}src/Behat/Behat/Transformation/Context/Annotation/TransformationAnnotationReader.php:XX
        ⚠ Using annotations to define steps is deprecated and will be removed in Behat 4.0. Use PHP attributes instead. (4x)
          → {BASE_PATH}src/Behat/Behat/Definition/Context/Annotation/DefinitionAnnotationReader.php:XX
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
    And the following deprecations should be reported:
      """
      7 deprecations triggered (2 unique):

        ⚠ Using annotations to define transformations is deprecated and will be removed in Behat 4.0. Use PHP attributes instead. (3x)
          → {BASE_PATH}src/Behat/Behat/Transformation/Context/Annotation/TransformationAnnotationReader.php:XX
        ⚠ Using annotations to define steps is deprecated and will be removed in Behat 4.0. Use PHP attributes instead. (4x)
          → {BASE_PATH}src/Behat/Behat/Definition/Context/Annotation/DefinitionAnnotationReader.php:XX
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
    And the following deprecations should be reported:
      """
      13 deprecations triggered (1 unique):

        ⚠ Using annotations to define transformations is deprecated and will be removed in Behat 4.0. Use PHP attributes instead. (13x)
          → {BASE_PATH}src/Behat/Behat/Transformation/Context/Annotation/TransformationAnnotationReader.php:XX
      """

  Scenario: Ordinal Arguments without quotes Transformations
    When I run behat with the following additional options:
      | option         | value                                 |
      | --suite        | ordinal_argument_transformation |
    Then it should pass with:
      """
      ........

      4 scenarios (4 passed)
      8 steps (8 passed)
      """
    And the following deprecations should be reported:
      """
      3 deprecations triggered (2 unique):

        ⚠ Using annotations to define transformations is deprecated and will be removed in Behat 4.0. Use PHP attributes instead.
          → {BASE_PATH}src/Behat/Behat/Transformation/Context/Annotation/TransformationAnnotationReader.php:XX
        ⚠ Using annotations to define steps is deprecated and will be removed in Behat 4.0. Use PHP attributes instead. (2x)
          → {BASE_PATH}src/Behat/Behat/Definition/Context/Annotation/DefinitionAnnotationReader.php:XX
      """

  Scenario: By-type transformations don't trigger from union types
    When I run behat with the following additional options:
      | option         | value                        |
      | --suite        | by_type_union_transformation |
    Then it should fail with:
      """
      must be of type User, string given
      """
    And the following deprecations should be reported:
      """
      3 deprecations triggered (2 unique):

        ⚠ Using annotations to define transformations is deprecated and will be removed in Behat 4.0. Use PHP attributes instead.
          → {BASE_PATH}src/Behat/Behat/Transformation/Context/Annotation/TransformationAnnotationReader.php:XX
        ⚠ Using annotations to define steps is deprecated and will be removed in Behat 4.0. Use PHP attributes instead. (2x)
          → {BASE_PATH}src/Behat/Behat/Definition/Context/Annotation/DefinitionAnnotationReader.php:XX
      """

  Scenario: Return type transformations don't cause issues with scalar type hints (regression)
    When I run behat with the following additional options:
      | option         | value                      |
      | --suite        | scalar_type_transformation |
    Then it should pass
    And the following deprecations should be reported:
      """
      2 deprecations triggered (2 unique):

        ⚠ Using annotations to define transformations is deprecated and will be removed in Behat 4.0. Use PHP attributes instead.
          → {BASE_PATH}src/Behat/Behat/Transformation/Context/Annotation/TransformationAnnotationReader.php:XX
        ⚠ Using annotations to define steps is deprecated and will be removed in Behat 4.0. Use PHP attributes instead.
          → {BASE_PATH}src/Behat/Behat/Definition/Context/Annotation/DefinitionAnnotationReader.php:XX
      """
