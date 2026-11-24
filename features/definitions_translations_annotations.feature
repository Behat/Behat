@expects-deprecations
Feature: Definitions Translations Annotations
  In order to be able to use predefined steps in native language
  As a step definitions developer
  I need to be able to write definition translations using annotations

  Background:
    Given I initialise the working directory from the "DefinitionsTranslations" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value       |
      | --no-colors |             |
      | --format    | progress    |
      | --profile   | annotations |

  Scenario: In place XLIFF translations
    When I run behat with the following additional options:
      | option  | value |
      | --suite | xliff |
    Then it should pass with:
      """
      .....

      1 scenario (1 passed)
      5 steps (5 passed)
      """
    And the following deprecations should be reported:
      """
      5 deprecations triggered (2 unique):

        ⚠ Using annotations to define steps is deprecated and will be removed in Behat 4.0. Use PHP attributes instead. (4x)
          → {BASE_PATH}src/Behat/Behat/Definition/Context/Annotation/DefinitionAnnotationReader.php:XX
        ⚠ Using annotations to define transformations is deprecated and will be removed in Behat 4.0. Use PHP attributes instead.
          → {BASE_PATH}src/Behat/Behat/Transformation/Context/Annotation/TransformationAnnotationReader.php:XX
      """

  Scenario: In place YAML translations
    When I run behat with the following additional options:
      | option  | value |
      | --suite | yaml  |
    Then it should pass with:
      """
      .....

      1 scenario (1 passed)
      5 steps (5 passed)
      """
    And the following deprecations should be reported:
      """
      5 deprecations triggered (2 unique):

        ⚠ Using annotations to define steps is deprecated and will be removed in Behat 4.0. Use PHP attributes instead. (4x)
          → {BASE_PATH}src/Behat/Behat/Definition/Context/Annotation/DefinitionAnnotationReader.php:XX
        ⚠ Using annotations to define transformations is deprecated and will be removed in Behat 4.0. Use PHP attributes instead.
          → {BASE_PATH}src/Behat/Behat/Transformation/Context/Annotation/TransformationAnnotationReader.php:XX
      """

  Scenario: In place PHP translations
    When I run behat with the following additional options:
      | option  | value |
      | --suite | php   |
    Then it should pass with:
      """
      .....

      1 scenario (1 passed)
      5 steps (5 passed)
      """
    And the following deprecations should be reported:
      """
      5 deprecations triggered (2 unique):

        ⚠ Using annotations to define steps is deprecated and will be removed in Behat 4.0. Use PHP attributes instead. (4x)
          → {BASE_PATH}src/Behat/Behat/Definition/Context/Annotation/DefinitionAnnotationReader.php:XX
        ⚠ Using annotations to define transformations is deprecated and will be removed in Behat 4.0. Use PHP attributes instead.
          → {BASE_PATH}src/Behat/Behat/Transformation/Context/Annotation/TransformationAnnotationReader.php:XX
      """

  Scenario: Translations with arguments without quotes
    When I run behat with the following additional options:
      | option  | value     |
      | --suite | arguments |
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
