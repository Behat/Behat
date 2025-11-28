Feature: Profile extension overrides
    In order to organize my profiles
    As a tester
    I need to be able to override extensions in non-default profiles

  Background:
    Given I initialise the working directory from the "ExtensionInheritance" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value |
      | --no-colors |       |

  Scenario: Default profile has extension loaded
    When I run behat with the following additional options:
      | option                                 | value |
      | --config behat-default-extension.yaml  |       |
      | features/extensions-default.feature    |       |
    Then it should pass

  Scenario: Custom profile disables all extensions
    When I run behat with the following additional options:
      | option                                 | value          |
      | --config behat-default-extension.yaml  |                |
      | --profile                              | custom_profile |
      | -vvv                                   |                |
      | features/extensions-disabled.feature   |                |
    Then it should pass

  Scenario: Custom profile has an additional extension
    When I run behat with the following additional options:
      | option                                   | value          |
      | --config behat-additional-extension.yaml |                |
      | --profile                                | custom_profile |
      | -vvv                                     |                |
      | features/extensions-additional.feature   |                |
    Then it should pass
