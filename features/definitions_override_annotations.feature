@expects-deprecations
Feature: Step Definitions Override Annotations
  In order to fine-tune definitions defined in parent classes
  As a step definitions developer
  I need to be able to override definition methods using step definition annotations

  Background:
    Given I initialise the working directory from the "DefinitionsOverride" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value                 |
      | --no-colors |                       |
      | --format    | progress              |
      | --config    | behat-annotations.php |

  Scenario: Overridden method without own annotation will inherit parent pattern
    When I run behat with the following additional options:
      | option                         | value   |
      | --profile                      | inherit |
      | features/step_patterns.feature |         |
    Then it should pass with:
      """
      .

      1 scenario (1 passed)
      1 step (1 passed)
      """
    And the following deprecations should be reported:
      """
      1 deprecation triggered (1 unique):

        ⚠ Using annotations to define steps is deprecated and will be removed in Behat 4.0. Use PHP attributes instead.
          → {BASE_PATH}src/Behat/Behat/Definition/Context/Annotation/DefinitionAnnotationReader.php:XX
      """

  Scenario: Overridden method with different annotation will have both patterns
    When I run behat with the following additional options:
      | option                              | value         |
      | --profile                           | both_patterns |
      | features/step_patterns_both.feature |               |
    Then it should pass with:
      """
      ..

      1 scenario (1 passed)
      2 steps (2 passed)
      """
    And the following deprecations should be reported:
      """
      2 deprecations triggered (1 unique):

        ⚠ Using annotations to define steps is deprecated and will be removed in Behat 4.0. Use PHP attributes instead. (2x)
          → {BASE_PATH}src/Behat/Behat/Definition/Context/Annotation/DefinitionAnnotationReader.php:XX
      """
