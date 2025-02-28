Feature: Convert config
  In order to migrate the existing yaml configuration
  As a developer
  I need to be able to convert this configuration to the new PHP format

  Background:
    Given I set the working directory to the "ConvertConfig" fixtures folder
    And I clear the default behat options
    And I provide the following options for all behat invocations:
      | option           | value |
      | --no-colors      |       |
      | --convert-config |       |

  Scenario: Convert empty file
    When I copy the "empty.yaml" file to the temp folder
    When I run behat with the following additional options:
      | option   | value                          |
      | --config | {SYSTEM_TMP_DIR}/empty.yaml |
    Then the temp "empty.php" file should be like:
      """
      <?php

      use Behat\Config\Config;

      return new Config();
      """
    And the temp "empty.yaml" file should have been removed

  Scenario: Convert profiles
    When I copy the "profiles.yaml" file to the temp folder
    When I run behat with the following additional options:
      | option   | value                          |
      | --config | {SYSTEM_TMP_DIR}/profiles.yaml |
    Then the temp "profiles.php" file should be like:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Profile;

      return (new Config())
          ->withProfile(new Profile('default'))
          ->withProfile(new Profile('another'));
      """
    And the temp "profiles.yaml" file should have been removed

  Scenario: Preferred profile
    When I copy the "preferred_profile.yaml" file to the temp folder
    When I run behat with the following additional options:
      | option   | value                          |
      | --config | {SYSTEM_TMP_DIR}/preferred_profile.yaml |
    Then the temp "preferred_profile.php" file should be like:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Profile;

      return (new Config())
          ->withProfile(new Profile('default'))
          ->withProfile((new Profile('another'))
              ->disableFormatter('pretty'))
          ->withPreferredProfile('another');
      """
    And the temp "preferred_profile.yaml" file should have been removed

  Scenario: Imports
    When I copy the "imports.yaml" file to the temp folder
    And I copy the "imported.yaml" file to the temp folder
    When I run behat with the following additional options:
      | option   | value                          |
      | --config | {SYSTEM_TMP_DIR}/imports.yaml |
    Then the temp "imports.php" file should be like:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Profile;

      return (new Config())
          ->import('imported.php')
          ->withProfile(new Profile('default'));
    """
    And the temp "imported.php" file should be like:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Profile;

      return (new Config())
          ->withProfile(new Profile('another'));
      """
    And the temp "imports.yaml" file should have been removed
    And the temp "imported.yaml" file should have been removed

  Scenario: Multiple Imports
    When I copy the "multiple_imports.yaml" file to the temp folder
    And I copy the "imported.yaml" file to the temp folder
    And I copy the "other_imported.yaml" file to the temp folder
    When I run behat with the following additional options:
      | option   | value                          |
      | --config | {SYSTEM_TMP_DIR}/multiple_imports.yaml |
    Then the temp "multiple_imports.php" file should be like:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Profile;

      return (new Config())
          ->import([
              'imported.php',
              'other_imported.php',
          ])
          ->withProfile(new Profile('default'));
    """
    And the temp "imported.php" file should be like:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Profile;

      return (new Config())
          ->withProfile(new Profile('another'));
      """
    And the temp "other_imported.php" file should be like:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Profile;

      return (new Config())
          ->withProfile(new Profile('other'));
      """
    And the temp "imports.yaml" file should have been removed
    And the temp "imported.yaml" file should have been removed
    And the temp "other_imported.yaml" file should have been removed

  Scenario: Suites
    When I copy the "suites.yaml" file to the temp folder
    When I run behat with the following additional options:
      | option   | value                          |
      | --config | {SYSTEM_TMP_DIR}/suites.yaml |
    Then the temp "suites.php" file should be like:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Profile;
      use Behat\Config\Suite;

      return (new Config())
          ->withProfile((new Profile('default'))
              ->withSuite(new Suite('one_suite'))
              ->withSuite(new Suite('another_suite')));
      """
    And the temp "suites.yaml" file should have been removed

  Scenario: Suite contexts
    When I copy the "suite_contexts.yaml" file to the temp folder
    When I run behat with the following additional options:
      | option   | value                          |
      | --config | {SYSTEM_TMP_DIR}/suite_contexts.yaml |
    Then the temp "suite_contexts.php" file should be like:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Profile;
      use Behat\Config\Suite;

      return (new Config())
          ->withProfile((new Profile('default'))
              ->withSuite((new Suite('my_suite'))
                  ->withContexts(
                      'MyContext',
                      'App\AnotherContext'
                  )));
      """
    And the temp "suite_contexts.yaml" file should have been removed

  Scenario: Suite paths
    When I copy the "suite_paths.yaml" file to the temp folder
    When I run behat with the following additional options:
      | option   | value                          |
      | --config | {SYSTEM_TMP_DIR}/suite_paths.yaml |
    Then the temp "suite_paths.php" file should be like:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Profile;
      use Behat\Config\Suite;

      return (new Config())
          ->withProfile((new Profile('default'))
              ->withSuite((new Suite('my_suite'))
                  ->withPaths(
                      'one.feature',
                      'other.feature'
                  )));
      """
    And the temp "suite_paths.yaml" file should have been removed

  Scenario: Suite filters
    When I copy the "suite_filters.yaml" file to the temp folder
    When I run behat with the following additional options:
      | option   | value                          |
      | --config | {SYSTEM_TMP_DIR}/suite_filters.yaml |
    Then the temp "suite_filters.php" file should be like:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Filter\TagFilter;
      use Behat\Config\Profile;
      use Behat\Config\Suite;

      return (new Config())
          ->withProfile((new Profile('default'))
              ->withSuite((new Suite('my_suite'))
                  ->withFilter(new TagFilter('@run'))));
      """
    And the temp "suite_filters.yaml" file should have been removed

  Scenario: Extensions
    When I copy the "extensions.yaml" file to the temp folder
    When I run behat with the following additional options:
      | option   | value                          |
      | --config | {SYSTEM_TMP_DIR}/extensions.yaml |
    Then the temp "extensions.php" file should be like:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Extension;
      use Behat\Config\Profile;

      return (new Config())
          ->withProfile((new Profile('default'))
              ->withExtension(new Extension('custom_extension.php', [
                  'property' => 'value',
                  'other_properties' => [
                      'my_tags' => [
                          'one_tag',
                          'another_tag',
                      ],
                  ],
              ])));
      """
    And the temp "extensions.yaml" file should have been removed

  Scenario: Profile filters
    When I copy the "profile_filters.yaml" file to the temp folder
    When I run behat with the following additional options:
      | option   | value                          |
      | --config | {SYSTEM_TMP_DIR}/profile_filters.yaml |
    Then the temp "profile_filters.php" file should be like:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Filter\NameFilter;
      use Behat\Config\Filter\RoleFilter;
      use Behat\Config\Profile;

      return (new Config())
          ->withProfile((new Profile('default'))
              ->withFilter(new NameFilter('john'))
              ->withFilter(new RoleFilter('admin')));
      """
    And the temp "profile_filters.yaml" file should have been removed

  Scenario: Unused definitions
    When I copy the "unused_definitions.yaml" file to the temp folder
    When I run behat with the following additional options:
      | option   | value                          |
      | --config | {SYSTEM_TMP_DIR}/unused_definitions.yaml |
    Then the temp "unused_definitions.php" file should be like:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Profile;

      return (new Config())
          ->withProfile((new Profile('default'))
              ->withPrintUnusedDefinitions(true));
      """
    And the temp "unused_definitions.yaml" file should have been removed

  Scenario: Formatters
    When I copy the "formatters.yaml" file to the temp folder
    When I run behat with the following additional options:
      | option   | value                          |
      | --config | {SYSTEM_TMP_DIR}/formatters.yaml |
    Then the temp "formatters.php" file should be like:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Extension;
      use Behat\Config\Formatter\Formatter;
      use Behat\Config\Formatter\PrettyFormatter;
      use Behat\Config\Formatter\ProgressFormatter;
      use Behat\Config\Profile;

      return (new Config())
          ->withProfile((new Profile('default'))
              ->withFormatter((new PrettyFormatter(paths: false))
                  ->withOutputDecorated(false))
              ->withFormatter(new ProgressFormatter())
              ->disableFormatter('junit')
              ->withFormatter((new Formatter('custom_formatter', [
                  'other_property' => 'value',
              ]))
                  ->withOutputVerbosity(2))
              ->withExtension(new Extension('custom_extension.php')));
      """
    And the temp "formatters.yaml" file should have been removed

  Scenario: Full configuration
    When I copy the "full_configuration.yaml" file to the temp folder
    And I copy the "imported.yaml" file to the temp folder
    When I run behat with the following additional options:
      | option   | value                          |
      | --config | {SYSTEM_TMP_DIR}/full_configuration.yaml |
    Then the temp "full_configuration.php" file should be like:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Extension;
      use Behat\Config\Filter\NameFilter;
      use Behat\Config\Filter\RoleFilter;
      use Behat\Config\Filter\TagFilter;
      use Behat\Config\Formatter\Formatter;
      use Behat\Config\Formatter\PrettyFormatter;
      use Behat\Config\Formatter\ProgressFormatter;
      use Behat\Config\Profile;
      use Behat\Config\Suite;

      return (new Config())
          ->import('imported.php')
          ->withProfile((new Profile('default'))
              ->withFormatter((new PrettyFormatter(paths: false))
                  ->withOutputDecorated(false))
              ->withFormatter(new ProgressFormatter())
              ->disableFormatter('junit')
              ->withFormatter((new Formatter('custom_formatter', [
                  'other_property' => 'value',
              ]))
                  ->withOutputVerbosity(2))
              ->withFilter(new NameFilter('john'))
              ->withFilter(new RoleFilter('admin'))
              ->withPrintUnusedDefinitions(true)
              ->withExtension(new Extension('custom_extension.php'))
              ->withSuite((new Suite('my_suite'))
                  ->withContexts('MyContext')
                  ->withPaths('one.feature')
                  ->withFilter(new TagFilter('@run'))))
          ->withProfile((new Profile('other'))
              ->disableFormatter('pretty'))
          ->withPreferredProfile('other');
      """
    And the temp "imported.php" file should be like:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Profile;

      return (new Config())
          ->withProfile(new Profile('another'));
      """
    And the temp "full_configuration.yaml" file should have been removed
    And the temp "imported.yaml" file should have been removed

