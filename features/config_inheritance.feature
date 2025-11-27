Feature: Config inheritance
  In order to avoid configuration duplication on each system
  As a context developer
  I need to be able to import base config from system-specific

  Background:
    Given I initialise the working directory from the "ConfigInheritance" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value |
      | --no-colors |       |

  Scenario: Config should successfully inherit parent one for default profiles
    When I run "behat features/configs.feature"
    Then it should pass with:
      """
      Feature:

        Scenario:                                           # features/configs.feature:2
          When this scenario executes                       # FeatureContext::thisScenarioExecutes()
          Then the context parameters should be overwritten # FeatureContext::theContextParametersOverwrite()
          And the extension config should be merged         # FeatureContext::theExtensionConfigMerge()

      1 scenario (1 passed)
      3 steps (3 passed)
      """

  Scenario: Config should successfully inherit parent one for custom profiles
    When I run "behat --profile custom_profile features/configs.feature"
    Then it should pass with:
      """
      Feature:

        Scenario:                                           # features/configs.feature:2
          When this scenario executes                       # FeatureContext::thisScenarioExecutes()
          Then the context parameters should be overwritten # FeatureContext::theContextParametersOverwrite()
          And the extension config should be merged         # FeatureContext::theExtensionConfigMerge()

      1 scenario (1 passed)
      3 steps (3 passed)
      """
