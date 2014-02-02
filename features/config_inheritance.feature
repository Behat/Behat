Feature: Config inheritance
  In order to avoid configuration duplication on each system
  As a context developer
  I need to be able to import base config from system-specific

  Background:
    Given a file named "behat.yml" with:
      """
      imports:
        - behat.yml.dist

      default:
        suites:
          default:
            contexts:
              - FeatureContext: [ { param2: val2 } ]
        extensions:
          custom_extension.php:
            param1: val2

      custom_profile:
        suites:
          default:
            contexts:
              - FeatureContext: [ { param2: val2 } ]
        extensions:
          custom_extension.php:
            param1: val2
      """
    Given a file named "behat.yml.dist" with:
      """
      default:
        suites:
          default:
            contexts:
              - FeatureContext: [ { param1: val1, param2: val1 } ]
        extensions:
          custom_extension.php:
            param1: val1
            param2: val1

      custom_profile:
        suites:
          default:
            contexts:
              - FeatureContext: [ { param1: val1, param2: val1 } ]
        extensions:
          custom_extension.php:
            param1: val1
            param2: val1
      """
    And a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          private $parameters;
          private $extension;

          public function __construct(array $parameters) {
              $this->parameters = $parameters;
          }

          public function setExtensionParameters(array $parameters) {
              $this->extension = $parameters;
          }

          /** @When this scenario executes */
          public function thisScenarioExecutes() {}

          /** @Then the context parameters should be overwritten */
          public function theContextParametersOverwrite() {
              \PHPUnit_Framework_Assert::assertEquals(array('param2' => 'val2'), $this->parameters);
          }

          /** @Then the extension config should be merged */
          public function theExtensionConfigMerge() {
              \PHPUnit_Framework_Assert::assertEquals(array('param1' => 'val2', 'param2' => 'val1'), $this->extension);
          }
      }
      """
    And a file named "custom_extension.php" with:
      """
      <?php

      use Symfony\Component\DependencyInjection\ContainerBuilder;
      use Behat\Behat\Context\Context;
      use Behat\Behat\Context\Initializer\ContextInitializer;
      use Behat\Testwork\ServiceContainer\Extension;
      use Behat\Testwork\ServiceContainer\ExtensionManager;
      use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

      class CustomInitializer implements ContextInitializer
      {
          private $parameters;

          public function __construct(array $parameters)
          {
              $this->parameters = $parameters;
          }

          public function supportsContext(Context $context)
          {
              return true;
          }

          public function initializeContext(Context $context)
          {
              $context->setExtensionParameters($this->parameters);
          }
      }

      class CustomExtension implements Extension
      {
          public function getConfigKey()
          {
              return 'custom';
          }

          public function configure(ArrayNodeDefinition $builder)
          {
              $builder->useAttributeAsKey('name')->prototype('variable');
          }

          public function initialize(ExtensionManager $extensionManager) {}

          public function load(ContainerBuilder $container, array $config)
          {
              $definition = $container->register('custom_initializer', 'CustomInitializer');
              $definition->setArguments(array($config));
              $definition->addTag('context.initializer', array('priority' => 100));
          }

          public function process(ContainerBuilder $container) {}
      }

      return new CustomExtension;
      """
    And a file named "features/configs.feature" with:
      """
      Feature:
        Scenario:
          When this scenario executes
          Then the context parameters should be overwritten
          And the extension config should be merged
      """

  Scenario: Config should successfully inherit parent one for default profiles
    When I run "behat -f progress --append-snippets"
    Then it should pass

  Scenario: Config should successfully inherit parent one for custom profiles
    When I run "behat -f progress --append-snippets --profile custom_profile"
    Then it should pass
