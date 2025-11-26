Feature: Helper services autowire
  In order to speed up the development process at early stages
  developers need to have a convenient way of requesting services without going through the explicit configuration layer

  Rules:
    - Autowiring only works with helper containers
    - Autowiring is off by default
    - Autowiring is enabled/disabled by a suite-level `autowire` flag
    - It works for context constructor arguments
    - It works for step definition arguments
    - It works for transformation arguments
    - It only wires arguments that weren't otherwise set
    - Services must be last arguments in step definitions
    - Services must be last arguments in transformations
    - Autowiring is not yet triggered for union types

  Background:
    Given I initialise the working directory from the "Autowire" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value    |
      | --no-colors |          |
      | --format    | progress |

  Scenario: Constructor arguments
    When I run behat with the following additional options:
      | option                    | value            |
      | --profile                 | constructor_args |
      | features/autowire.feature |                  |
    Then it should pass

  Scenario: Mixed constructor arguments
    When I run behat with the following additional options:
      | option                    | value                  |
      | --profile                 | mixed_constructor_args |
      | features/autowire.feature |                        |
    Then it should pass

  Scenario: Null arguments should be skipped
    When I run behat with the following additional options:
      | option                    | value             |
      | --profile                 | null_args         |
      | features/autowire.feature |                   |
    Then it should pass

  Scenario: Unregistered services as constructor arguments
    When I run behat with the following additional options:
      | option                    | value                    |
      | --profile                 | unregistered_constructor |
      | features/autowire.feature |                          |
    Then it should fail with:
      """
      Service Service4 not found
      """

  Scenario: Step definition arguments
    When I run behat with the following additional options:
      | option                            | value                |
      | --profile                         | step_definition_args |
      | features/step-definitions.feature |                      |
    Then it should pass

  Scenario: Unregistered step definition argument
    When I run behat with the following additional options:
      | option                    | value             |
      | --profile                 | unregistered_step |
      | features/autowire.feature |                   |
    Then it should fail with:
      """
      Service Service4 not found
      """

  Scenario: Transformation arguments
    When I run behat with the following additional options:
      | option                           | value               |
      | --profile                        | transformation_args |
      | features/transformations.feature |                     |
    Then it should pass

  Scenario: Union constructor arguments
    When I run behat with the following additional options:
      | option                    | value                  |
      | --profile                 | union_constructor_args |
      | features/autowire.feature |                        |
    Then it should fail with:
      """
      Can not find a matching value for an argument `$s` of the method `UnionConstructorArgsContext::__construct()`
      """
