Feature: Config inheritance
  In order to avoid configuration duplication on each system
  As a context developer
  I need to be able to import base config from system-specific

  Background:
    Given I initialise the working directory from the "ConfigInheritance" fixtures folder
    And I provide the following options for all behat invocations:
      | option            | value    |
      | --no-colors       |          |
      | --format          | progress |
      | --append-snippets |          |

  Scenario: Config should successfully inherit parent one for default profiles
    When I run "behat features/configs.feature"
    Then the output should contain:
    """
    testing
    """

  Scenario: Config should successfully inherit parent one for custom profiles
    When I run "behat --profile custom_profile features/configs.feature"
    Then the output should contain:
    """
    testing
    """
