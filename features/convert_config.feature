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
      | option   | value                       |
      | --config | {SYSTEM_TMP_DIR}/empty.yaml |
    Then it should pass
    And the temp "empty.php" file should be like:
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
    Then it should pass
    And the temp "profiles.php" file should be like:
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
      | option   | value                                   |
      | --config | {SYSTEM_TMP_DIR}/preferred_profile.yaml |
    Then it should pass
    And the temp "preferred_profile.php" file should be like:
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
      | option   | value                         |
      | --config | {SYSTEM_TMP_DIR}/imports.yaml |
    Then it should pass
    And the temp "imports.php" file should be like:
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
      | option   | value                                  |
      | --config | {SYSTEM_TMP_DIR}/multiple_imports.yaml |
    Then it should pass
    And the temp "multiple_imports.php" file should be like:
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
      | option   | value                        |
      | --config | {SYSTEM_TMP_DIR}/suites.yaml |
    Then it should pass
    And the temp "suites.php" file should be like:
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
      | option   | value                                |
      | --config | {SYSTEM_TMP_DIR}/suite_contexts.yaml |
    Then it should pass
    And the temp "suite_contexts.php" file should be like:
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
                      'AnotherContext'
                  )));
      """
    And the temp "suite_contexts.yaml" file should have been removed

  Scenario: Suite contexts with arguments
    When I copy the "suite_contexts_with_args.yaml" file to the temp folder
    When I run behat with the following additional options:
      | option   | value                                          |
      | --config | {SYSTEM_TMP_DIR}/suite_contexts_with_args.yaml |
    Then it should pass
    And the temp "suite_contexts_with_args.php" file should be like:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Profile;
      use Behat\Config\Suite;

      return (new Config())
          ->withProfile((new Profile('default'))
              ->withSuite((new Suite('my_suite'))
                  ->addContext('MyContext')
                  ->addContext(
                      'AContextWithPositionalArgs',
                      [
                          'First Arg',
                          'Second Arg',
                      ]
                  )
                  ->addContext(
                      'AContextWithNamedArgs',
                      [
                          'param1' => 'Something',
                          'param2' => 'Else',
                      ]
                  )
                  ->addContext('AnotherContext')));
      """
    And the temp "suite_contexts.yaml" file should have been removed

  Scenario: Suite paths
    When I copy the "suite_paths.yaml" file to the temp folder
    When I run behat with the following additional options:
      | option   | value                             |
      | --config | {SYSTEM_TMP_DIR}/suite_paths.yaml |
    Then it should pass
    And the temp "suite_paths.php" file should be like:
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
      | option   | value                               |
      | --config | {SYSTEM_TMP_DIR}/suite_filters.yaml |
    Then it should pass
    And the temp "suite_filters.php" file should be like:
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
      | option   | value                            |
      | --config | {SYSTEM_TMP_DIR}/extensions.yaml |
    Then it should pass
    And the temp "extensions.php" file should be like:
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

  Scenario: Class references for known extensions and contexts
    Given I copy the "class_references.yaml" file to the temp folder
    When  I run behat with the following additional options:
      | option   | value                                  |
      | --config | {SYSTEM_TMP_DIR}/class_references.yaml |
    Then it should pass
    And the temp "class_references.php" file should be like:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Extension;
      use Behat\Config\Profile;
      use Behat\Config\Suite;
      use MyContext;
      use Some\Behat\Extension\ExplicitlyReferencedExtension;
      use Some\ShorthandExtension\ServiceContainer\ShorthandExtension;
      use test\MyApp\Contexts\MyFirstContext;
      use test\MyApp\Contexts\MySecondContext;

      return (new Config())
          ->withProfile((new Profile('default'))
              ->withExtension(new Extension('class_references_loader.php'))
              ->withExtension(new Extension(ExplicitlyReferencedExtension::class))
              ->withExtension(new Extension(ShorthandExtension::class))
              ->withSuite((new Suite('named_contexts'))
                  ->withContexts(
                      'UnknownContext',
                      MyContext::class,
                      MyFirstContext::class,
                      MySecondContext::class
                  ))
              ->withSuite((new Suite('contexts_with_args'))
                  ->addContext('UnknownContext')
                  ->addContext(
                      MyFirstContext::class,
                      [
                          'param1',
                      ]
                  )
                  ->addContext(MySecondContext::class)));
      """
    And the temp "class_references.yaml" file should have been removed

  Scenario: Profile filters
    When I copy the "profile_filters.yaml" file to the temp folder
    When I run behat with the following additional options:
      | option   | value                                 |
      | --config | {SYSTEM_TMP_DIR}/profile_filters.yaml |
    Then it should pass
    And the temp "profile_filters.php" file should be like:
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
      | option   | value                                    |
      | --config | {SYSTEM_TMP_DIR}/unused_definitions.yaml |
    Then it should pass
    And the temp "unused_definitions.php" file should be like:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Profile;

      return (new Config())
          ->withProfile((new Profile('default'))
              ->withPrintUnusedDefinitions());
      """
    And the temp "unused_definitions.yaml" file should have been removed

  Scenario: Formatters
    When I copy the "formatters.yaml" file to the temp folder
    When I run behat with the following additional options:
      | option   | value                            |
      | --config | {SYSTEM_TMP_DIR}/formatters.yaml |
    Then it should pass
    And the temp "formatters.php" file should be like:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Extension;
      use Behat\Config\Formatter\Formatter;
      use Behat\Config\Formatter\JUnitFormatter;
      use Behat\Config\Formatter\PrettyFormatter;
      use Behat\Config\Formatter\ProgressFormatter;
      use Behat\Config\Formatter\ShowOutputOption;
      use Behat\Config\Profile;
      use Behat\Testwork\Output\Printer\Factory\OutputFactory;

      return (new Config())
          ->withProfile((new Profile('default'))
              ->withFormatter((new PrettyFormatter(paths: false))
                  ->withOutputDecorated(false))
              ->withFormatter(new ProgressFormatter())
              ->disableFormatter('junit')
              ->withFormatter((new Formatter('custom_formatter', [
                  'other_property' => 'value',
              ]))
                  ->withOutputVerbosity(OutputFactory::VERBOSITY_VERBOSE))
              ->withExtension(new Extension('custom_extension.php')))
          ->withProfile((new Profile('with_options'))
              ->withFormatter((new JUnitFormatter())
                  ->withOutputPath('build/logs/junit'))
              ->withFormatter((new ProgressFormatter(showOutput: ShowOutputOption::OnFail))
                  ->withOutputVerbosity(OutputFactory::VERBOSITY_VERY_VERBOSE))
              ->withFormatter((new PrettyFormatter(expand: true, showOutput: ShowOutputOption::No))
                  ->withOutputStyles([
                      'failed' => [
                          'white',
                          'red',
                          'blink',
                      ],
                  ])));
      """
    And the temp "formatters.yaml" file should have been removed

  Scenario: path options
    When I copy the "path_options.yaml" file to the temp folder
    When I run behat with the following additional options:
      | option   | value                                    |
      | --config | {SYSTEM_TMP_DIR}/path_options.yaml |
    Then it should pass
    And the temp "path_options.php" file should be like:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Profile;

      return (new Config())
          ->withProfile((new Profile('default'))
              ->withPathOptions(printAbsolutePaths: true))
          ->withProfile((new Profile('with_editor_url'))
              ->withPathOptions(editorUrl: 'phpstorm://open?file={relPath}&line={line}'))
          ->withProfile((new Profile('with_remove_prefix'))
              ->withPathOptions(removePrefix: [
                  'features/bootstrap/',
                  'features/',
              ]));
      """
    And the temp "path_options.yaml" file should have been removed

  Scenario: Tester options
    Given I copy the "tester_options.yaml" file to the temp folder
    When I run behat with the following additional options:
      | option   | value                                    |
      | --config | {SYSTEM_TMP_DIR}/tester_options.yaml |
    Then it should pass
    And the temp "tester_options.php" file should be like:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Profile;
      use Behat\Config\TesterOptions;

      return (new Config())
          ->withProfile(new Profile('default'))
          ->withProfile((new Profile('ignore-errors'))
              ->withTesterOptions((new TesterOptions())
                  ->withErrorReporting(E_ALL & ~E_DEPRECATED)))
          ->withProfile((new Profile('not-strict'))
              ->withTesterOptions((new TesterOptions())
                  ->withStrictResultInterpretation(false)))
          ->withProfile((new Profile('complete'))
              ->withTesterOptions((new TesterOptions())
                  ->withStrictResultInterpretation()
                  ->withStopOnFailure(false)
                  ->withSkipAllTests()
                  ->withErrorReporting(E_ALL & ~(E_WARNING | E_NOTICE | E_DEPRECATED))));
      """
    And the temp "tester_options.yaml" file should have been removed

  Scenario: Full configuration
    When I copy the "full_configuration.yaml" file to the temp folder
    And I copy the "imported.yaml" file to the temp folder
    And the "MY_SECRET_PASSWORD" environment variable is set to "sesame"
    When I run behat with the following additional options:
      | option   | value                                    |
      | --config | {SYSTEM_TMP_DIR}/full_configuration.yaml |
    Then it should pass
    And the temp "full_configuration.php" file should be like:
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
      use Behat\Config\TesterOptions;
      use Behat\Testwork\Output\Printer\Factory\OutputFactory;

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
                  ->withOutputVerbosity(OutputFactory::VERBOSITY_VERBOSE))
              ->withFilter(new NameFilter('john'))
              ->withFilter(new RoleFilter('admin'))
              ->withPrintUnusedDefinitions()
              ->withPathOptions(
                  printAbsolutePaths: true,
                  editorUrl: 'phpstorm://open?file={relPath}&line={line}',
                  removePrefix: [
                      'features/bootstrap/',
                      'features/',
                  ]
              )
              ->withTesterOptions((new TesterOptions())
                  ->withStrictResultInterpretation())
              ->withExtension(new Extension('custom_extension.php'))
              ->withSuite((new Suite('my_suite'))
                  ->addContext(
                      'MyContext',
                      [
                          'password' => '%env(MY_SECRET_PASSWORD)%',
                      ]
                  )
                  ->withPaths('one.feature')
                  ->withFilter(new TagFilter('@run'))))
          ->withProfile((new Profile('other'))
              ->disableFormatter('pretty')
              ->withTesterOptions((new TesterOptions())
                  ->withErrorReporting(E_ERROR)))
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
