Feature: Convert config
  In order to migrate the existing yaml configuration
  As a developer
  I need to be able to convert this configuration to the new PHP format

  Background:
    Given I initialise the working directory from the "ConvertConfig" fixtures folder
    And I clear the default behat options
    And I provide the following options for all behat invocations:
      | option           | value |
      | --no-colors      |       |
      | --convert-config |       |

  Scenario: Convert empty file
    When I run behat with the following additional options:
      | option   | value      |
      | --config | empty.yaml |
    Then it should pass
    And "empty.php" file should contain:
      """
      <?php

      use Behat\Config\Config;

      return new Config();
      """
    And the "empty.yaml" file should have been removed from the working directory

  Scenario: Convert profiles
    When I run behat with the following additional options:
      | option   | value         |
      | --config | profiles.yaml |
    Then it should pass
    And "profiles.php" file should contain:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Profile;

      return (new Config())
          ->withProfile(new Profile('default'))
          ->withProfile(new Profile('another'));
      """
    And the "profiles.yaml" file should have been removed from the working directory

  Scenario: Preferred profile
    When I run behat with the following additional options:
      | option   | value                  |
      | --config | preferred_profile.yaml |
    Then it should pass
    And "preferred_profile.php" file should contain:
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
    And the "preferred_profile.yaml" file should have been removed from the working directory

  Scenario: Imports
    When I run behat with the following additional options:
      | option   | value        |
      | --config | imports.yaml |
    Then it should pass
    And "imports.php" file should contain:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Profile;

      return (new Config())
          ->import('imported.php')
          ->withProfile(new Profile('default'));
    """
    And "imported.php" file should contain:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Profile;

      return (new Config())
          ->withProfile(new Profile('another'));
      """
    And the "imports.yaml" file should have been removed from the working directory
    And the "imported.yaml" file should have been removed from the working directory

  Scenario: Multiple Imports
    When I run behat with the following additional options:
      | option   | value                 |
      | --config | multiple_imports.yaml |
    Then it should pass with:
      """
      Starting conversion
      Converting configuration file: multiple_imports.yaml
      Converting configuration file: .%%DS%%imported.yaml
      Converting configuration file: .%%DS%%other_imported.yaml
      Conversion finished
      """
    And "multiple_imports.php" file should contain:
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
    And "imported.php" file should contain:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Profile;

      return (new Config())
          ->withProfile(new Profile('another'));
      """
    And "other_imported.php" file should contain:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Profile;

      return (new Config())
          ->withProfile(new Profile('other'));
      """
    And the "multiple_imports.yaml" file should have been removed from the working directory
    And the "imported.yaml" file should have been removed from the working directory
    And the "other_imported.yaml" file should have been removed from the working directory

  Scenario: Suites
    When I run behat with the following additional options:
      | option   | value       |
      | --config | suites.yaml |
    Then it should pass
    And "suites.php" file should contain:
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
    And the "suites.yaml" file should have been removed from the working directory

  Scenario: Suite contexts
    When I run behat with the following additional options:
      | option   | value               |
      | --config | suite_contexts.yaml |
    Then it should pass
    And "suite_contexts.php" file should contain:
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
    And the "suite_contexts.yaml" file should have been removed from the working directory

  Scenario: Suite contexts with arguments
    When I run behat with the following additional options:
      | option   | value                         |
      | --config | suite_contexts_with_args.yaml |
    Then it should pass
    And "suite_contexts_with_args.php" file should contain:
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
    And the "suite_contexts_with_args.yaml" file should have been removed from the working directory

  Scenario: Suite paths
    When I run behat with the following additional options:
      | option   | value            |
      | --config | suite_paths.yaml |
    Then it should pass
    And "suite_paths.php" file should contain:
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
    And the "suite_paths.yaml" file should have been removed from the working directory

  Scenario: Suite filters
    When I run behat with the following additional options:
      | option   | value              |
      | --config | suite_filters.yaml |
    Then it should pass
    And "suite_filters.php" file should contain:
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
    And the "suite_filters.yaml" file should have been removed from the working directory

  Scenario: Extensions
    When I run behat with the following additional options:
      | option   | value           |
      | --config | extensions.yaml |
    Then it should pass
    And "extensions.php" file should contain:
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
    And the "extensions.yaml" file should have been removed from the working directory

  Scenario: Class references for known extensions and contexts
    When  I run behat with the following additional options:
      | option   | value                 |
      | --config | class_references.yaml |
    Then it should pass
    And "class_references.php" file should contain:
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
    And the "class_references.yaml" file should have been removed from the working directory

  Scenario: Profile filters
    When I run behat with the following additional options:
      | option   | value                |
      | --config | profile_filters.yaml |
    Then it should pass
    And "profile_filters.php" file should contain:
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
    And the "profile_filters.yaml" file should have been removed from the working directory

  Scenario: Unused definitions
    When I run behat with the following additional options:
      | option   | value                   |
      | --config | unused_definitions.yaml |
    Then it should pass
    And "unused_definitions.php" file should contain:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Profile;

      return (new Config())
          ->withProfile((new Profile('default'))
              ->withPrintUnusedDefinitions());
      """
    And the "unused_definitions.yaml" file should have been removed from the working directory

  Scenario: Formatters
    When I run behat with the following additional options:
      | option   | value           |
      | --config | formatters.yaml |
    Then it should pass
    And "formatters.php" file should contain:
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
    And the "formatters.yaml" file should have been removed from the working directory

  Scenario: path options
    When I run behat with the following additional options:
      | option   | value             |
      | --config | path_options.yaml |
    Then it should pass
    And "path_options.php" file should contain exactly:
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
    And the "path_options.yaml" file should have been removed from the working directory

  Scenario: Tester options
    When I run behat with the following additional options:
      | option   | value               |
      | --config | tester_options.yaml |
    Then it should pass
    And "tester_options.php" file should contain:
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
    And the "tester_options.yaml" file should have been removed from the working directory

  Scenario: Full configuration
    And the "MY_SECRET_PASSWORD" environment variable is set to "sesame"
    When I run behat with the following additional options:
      | option   | value                   |
      | --config | full_configuration.yaml |
    Then it should pass
    And "full_configuration.php" file should contain exactly:
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
    And "imported.php" file should contain:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Profile;

      return (new Config())
          ->withProfile(new Profile('another'));
      """
    And the "full_configuration.yaml" file should have been removed from the working directory
    And the "imported.yaml" file should have been removed from the working directory
