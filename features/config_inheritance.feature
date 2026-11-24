@expects-deprecations
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
    And the following deprecations should be reported:
      """
      2 deprecations triggered (2 unique):

        ⚠ Loading YAML configuration files is deprecated and will be removed in Behat 4.0. Please convert "%%WORKING_DIR%%behat.yaml" to a PHP configuration file. You can use the --convert-config option to do this automatically.
          → {BASE_PATH}src/Behat/Testwork/ServiceContainer/Configuration/ConfigurationLoader.php:XX
        ⚠ Loading YAML configuration files is deprecated and will be removed in Behat 4.0. Please convert "behat.dist.yaml" to a PHP configuration file. You can use the --convert-config option to do this automatically.
          → {BASE_PATH}src/Behat/Testwork/ServiceContainer/Configuration/ConfigurationLoader.php:XX
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
    And the following deprecations should be reported:
      """
      2 deprecations triggered (2 unique):

        ⚠ Loading YAML configuration files is deprecated and will be removed in Behat 4.0. Please convert "%%WORKING_DIR%%behat.yaml" to a PHP configuration file. You can use the --convert-config option to do this automatically.
          → {BASE_PATH}src/Behat/Testwork/ServiceContainer/Configuration/ConfigurationLoader.php:XX
        ⚠ Loading YAML configuration files is deprecated and will be removed in Behat 4.0. Please convert "behat.dist.yaml" to a PHP configuration file. You can use the --convert-config option to do this automatically.
          → {BASE_PATH}src/Behat/Testwork/ServiceContainer/Configuration/ConfigurationLoader.php:XX
      """
