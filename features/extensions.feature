Feature: Extensions
  In order to provide additional functionality for Behat
  As a developer
  I need to be able to write simple extensions

  Background:
    Given a file named "behat.yml" with:
      """
      default:
        extensions:
          custom_extension.php:
            param1: val1
            param2: val2
      """
    And a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          private $extension;

          public function setExtensionParameters(array $parameters) {
              $this->extension = $parameters;
          }

          /** @When this scenario executes */
          public function thisScenarioExecutes() {}

          /** @Then the extension should be loaded */
          public function theExtensionLoaded() {
              PHPUnit_Framework_Assert::assertEquals(array('param1' => 'val1', 'param2' => 'val2'), $this->extension);
          }
      }
      """
    And a file named "custom_extension.php" with:
      """
      <?php

      use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
      use Symfony\Component\DependencyInjection\ContainerBuilder;

      class CustomInitializer implements Behat\Behat\Context\Initializer\ContextInitializer
      {
          private $parameters;

          public function __construct(array $parameters)
          {
              $this->parameters = $parameters;
          }

          public function supportsContext(Behat\Behat\Context\Context $context)
          {
              return true;
          }

          public function initializeContext(Behat\Behat\Context\Context $context)
          {
              $context->setExtensionParameters($this->parameters);
          }
      }

      class CustomExtension implements Behat\Testwork\ServiceContainer\Extension {
          public function getConfigKey()
          {
              return 'custom_extension';
          }

          public function configure(ArrayNodeDefinition $builder)
          {
              $builder->useAttributeAsKey('name')->prototype('variable');
          }

          public function initialize(Behat\Testwork\ServiceContainer\ExtensionManager $extensionManager) {}

          public function load(ContainerBuilder $container, array $config)
          {
              $definition = $container->register('custom_initializer', 'CustomInitializer', array($config));
              $definition->setArguments(array($config));
              $definition->addTag('context.initializer', array('priority' => 100));
          }

          public function process(ContainerBuilder $container) {}
      }

      return new CustomExtension;
      """
    And a file named "features/extensions.feature" with:
      """
      Feature:
        Scenario:
          When this scenario executes
          Then the extension should be loaded
      """

  Scenario: Extension should be successfully loaded
    When I run "behat -f progress --append-snippets"
    Then it should pass

  Scenario: Instantiating inexistent extension file
    Given a file named "behat.yml" with:
      """
      default:
        extensions:
          inexistent_extension: ~
      """
    And a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
      }
      """
    When I run "behat -f progress --no-colors"
    Then it should fail with:
      """
      [Behat\Testwork\ServiceContainer\Exception\ExtensionInitializationException]
        `inexistent_extension` extension file or class could not be located.
      """
