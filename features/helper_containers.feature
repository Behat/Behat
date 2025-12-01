Feature: Per-suite helper containers
  In order to share state and behaviour between contexts
  developers need to have a way to create or register shared service container

  Rules:
    - A single optional container is allowed per suite
    - Having a container enables you to use its services as context arguments via `@name` syntax
    - Container is rebuilt and is isolated between scenarios
    - Container is configured via suite's `services` option
    - Container is a class implementing `Psr\Container\ContainerInterface`
    - There is a built-in container if you need a very simple service-sharing, configurable through the same `services` setting
    - There is an extension point that allows Behat extensions provide their own containers for end-users via `@name` syntax

  Out of scope:
    - Extensive service configuration and deep dependency trees support for built-in container. Behat is not your DIC framework
    - Sharing scalar, non-object parameters using container. Use YAML anchors and references for configuration sharing
    - Multiple containers per suite. Would introduce unnecessary complexity. Also, easily achievable manually through composition
    - PSR-11 support. It was not accepted as a standard as of feature implementation. Support might be added later, subject to prioritisation

  Usage:
    - Use built-in container for simple dependency trees and when no DIC is used
    - Use external container (ideally the one used for application itself) for deep dependency trees
    - Use extension-provided containers when working with frameworks (if provided by framework)

  Background:
    Given I initialise the working directory from the "HelperContainers" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value    |
      | --no-colors |          |
      | --format    | pretty   |

  Scenario: External container
    When I run behat with the following additional options:
      | option                     | value                        |
      | --config                   | behat-external-container.php |
      | features/container.feature |                              |
    Then it should pass with:
      """
      Feature:

        Scenario:                                                 # features/container.feature:2
          Given service has no state                              # FirstContext::noState()
          When service gets a state of 1 in first context         # FirstContext::setState()
          Then service should have a state of 1 in second context # SecondContext::checkState()

        Scenario:                                                  # features/container.feature:7
          Given service has no state                               # FirstContext::noState()
          When service gets a state of 33 in first context         # FirstContext::setState()
          Then service should have a state of 33 in second context # SecondContext::checkState()

      2 scenarios (2 passed)
      6 steps (6 passed)
      """

  Scenario: Simplest built-in container configuration
    When I run behat with the following additional options:
      | option                     | value                       |
      | --config                   | behat-builtin-container.php |
      | features/container.feature |                             |
    Then it should pass with:
      """
      Feature:

        Scenario:                                                 # features/container.feature:2
          Given service has no state                              # FirstContext::noState()
          When service gets a state of 1 in first context         # FirstContext::setState()
          Then service should have a state of 1 in second context # SecondContext::checkState()

        Scenario:                                                  # features/container.feature:7
          Given service has no state                               # FirstContext::noState()
          When service gets a state of 33 in first context         # FirstContext::setState()
          Then service should have a state of 33 in second context # SecondContext::checkState()

      2 scenarios (2 passed)
      6 steps (6 passed)
      """

  Scenario: Built-in container with service arguments
    When I run behat with the following additional options:
      | option                     | value                              |
      | --config                   | behat-container-with-arguments.php |
      | features/container.feature |                                    |
    Then it should pass with:
      """
      Feature:

        Scenario:                                                 # features/container.feature:2
          Given service has no state                              # FirstContext::noState()
          When service gets a state of 1 in first context         # FirstContext::setState()
          Then service should have a state of 1 in second context # SecondContext::checkState()

        Scenario:                                                  # features/container.feature:7
          Given service has no state                               # FirstContext::noState()
          When service gets a state of 33 in first context         # FirstContext::setState()
          Then service should have a state of 33 in second context # SecondContext::checkState()

      2 scenarios (2 passed)
      6 steps (6 passed)
      """

  Scenario: Built-in container with factory-based services
    When I run behat with the following additional options:
      | option                     | value                            |
      | --config                   | behat-container-with-factory.php |
      | features/container.feature |                                  |
    Then it should pass with:
      """
      Feature:

        Scenario:                                                 # features/container.feature:2
          Given service has no state                              # FirstContext::noState()
          When service gets a state of 1 in first context         # FirstContext::setState()
          Then service should have a state of 1 in second context # SecondContext::checkState()

        Scenario:                                                  # features/container.feature:7
          Given service has no state                               # FirstContext::noState()
          When service gets a state of 33 in first context         # FirstContext::setState()
          Then service should have a state of 33 in second context # SecondContext::checkState()

      2 scenarios (2 passed)
      6 steps (6 passed)
      """

  Scenario: Container provided by an extension
    When I run behat with the following additional options:
      | option                     | value                        |
      | --config                   | behat-extension-provided.php |
      | features/container.feature |                              |
    Then it should pass with:
      """
      Feature:

        Scenario:                                                 # features/container.feature:2
          Given service has no state                              # FirstContext::noState()
          When service gets a state of 1 in first context         # FirstContext::setState()
          Then service should have a state of 1 in second context # SecondContext::checkState()

        Scenario:                                                  # features/container.feature:7
          Given service has no state                               # FirstContext::noState()
          When service gets a state of 33 in first context         # FirstContext::setState()
          Then service should have a state of 33 in second context # SecondContext::checkState()

      2 scenarios (2 passed)
      6 steps (6 passed)
      """

  Scenario: Mix of typehinted arguments and numbered arguments (fix #991)
    When I run behat with the following additional options:
      | option                          | value                |
      | --config                        | behat-mixed-args.php |
      | features/container_args.feature |                      |
    Then it should pass with:
      """
      Feature:

        Scenario:   # features/container_args.feature:2
          Given foo # FirstContextMixedArgs::foo()

      1 scenario (1 passed)
      1 step (1 passed)
      """

  Scenario: Injecting typehinted arguments for a parent and child class (fix #1008)
    When I run behat with the following additional options:
      | option                          | value                |
      | --config                        | behat-typehinted.php |
      | features/container_args.feature |                      |
    Then it should pass with:
      """
      Feature:

        Scenario:   # features/container_args.feature:2
          Given foo # FirstContextTypehinted::foo()

      1 scenario (1 passed)
      1 step (1 passed)
      """
