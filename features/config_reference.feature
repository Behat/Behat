Feature: Config reference
  In order to know the available configuration
  As a Behat user
  I need to be able to dump the configuration reference

  Scenario: Reference of defaults extension
    When I run "behat --no-colors --config-reference -v"
    Then it should pass with:
      """
      testwork:
          cli:                  []
          calls:
              error_reporting:      32767
          suites:

              # Prototype
              name:
                  enabled:              true
                  type:                 null
                  settings:

                      # Prototype
                      name:                 ~
          environments:         []
          specifications:       []
          events:               []
          filesystem:           []
          exceptions:           []
          autoload:

              # Default:
              :                    %paths.base%/features/bootstrap
          translation:
              locale:               en
              fallback_locale:      en
          gherkin:
              cache:                %%TMP_DIR%%gherkin_cache
              filters:

                  # Prototype
                  name:                 ~
          contexts:             []
          formatters:

              # Prototype
              name:                 []
          snippets:             []
          definitions:          []
          hooks:                []
          transformations:      []
          testers:
              strict:               false
              skip:                 false
      """

  Scenario: Custom extension
    Given a file named "behat.yml" with:
      """
      default:
        extensions:
          custom_extension.php: ~
      """
    And a file named "custom_extension.php" with:
      """
      <?php

      use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
      use Symfony\Component\DependencyInjection\ContainerBuilder;

      class CustomExtension implements Behat\Testwork\ServiceContainer\Extension {
          public function getConfigKey()
          {
              return 'custom_extension';
          }

          public function configure(ArrayNodeDefinition $builder)
          {
              $builder
                  ->children()
                      ->scalarNode('child')->info('A child node')->end()
                      ->booleanNode('test')->defaultTrue()->end()
                  ->end();
          }

          public function initialize(Behat\Testwork\ServiceContainer\ExtensionManager $extensionManager) {}

          public function load(ContainerBuilder $container, array $config) {}

          public function process(ContainerBuilder $container) {}
      }

      return new CustomExtension;
      """
    When I run "behat --no-colors --config-reference"
    Then it should pass with:
      """
      testwork:
          cli:                  []
          calls:
              error_reporting:      32767
          suites:

              # Prototype
              name:
                  enabled:              true
                  type:                 null
                  settings:

                      # Prototype
                      name:                 ~
          environments:         []
          specifications:       []
          events:               []
          filesystem:           []
          exceptions:           []
          autoload:

              # Default:
              :                    %paths.base%/features/bootstrap
          translation:
              locale:               en
              fallback_locale:      en
          gherkin:
              cache:                %%TMP_DIR%%gherkin_cache
              filters:

                  # Prototype
                  name:                 ~
          contexts:             []
          formatters:

              # Prototype
              name:                 []
          snippets:             []
          definitions:          []
          hooks:                []
          transformations:      []
          testers:
              strict:               false
              skip:                 false
          custom_extension:

              # A child node
              child:                ~
              test:                 true
      """
