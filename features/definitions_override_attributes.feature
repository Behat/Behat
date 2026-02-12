Feature: Step Definitions Override Attributes
  In order to fine-tune definitions defined in parent classes
  As a step definitions developer
  I need to be able to override definition methods using step definition attributes

  Background:
    Given I initialise the working directory from the "DefinitionsOverride" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value                |
      | --no-colors |                      |
      | --format    | progress             |
      | --config    | behat-attributes.php |

  Scenario: Overridden method without own attribute will inherit parent pattern
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

  Scenario: Overridden method with different attribute will have both patterns
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

