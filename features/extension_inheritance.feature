Feature: Profile extension overrides
    In order to organize my profiles
    As a tester
    I need to be able to override extensions in non-default profiles

  Background:
    Given a file named "custom_extension.php" with:
      """
      <?php

      use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
      use Symfony\Component\DependencyInjection\ContainerBuilder;

      class CustomInitializer implements Behat\Behat\Context\Initializer\ContextInitializer
      {
          public function supportsContext(Behat\Behat\Context\Context $context)
          {
              return true;
          }

          public function initializeContext(Behat\Behat\Context\Context $context)
          {
              $context->addExtension('custom extension 1');
          }
      }

      class CustomExtension implements Behat\Testwork\ServiceContainer\Extension {
          public function getConfigKey()
          {
              return 'custom_extension';
          }

          public function configure(ArrayNodeDefinition $builder) {}

          public function initialize(Behat\Testwork\ServiceContainer\ExtensionManager $extensionManager) {}

          public function load(ContainerBuilder $container, array $config)
          {
              $definition = $container->register('custom_initializer', 'CustomInitializer');
              $definition->addTag('context.initializer', array('priority' => 100));
          }

          public function process(ContainerBuilder $container) {}
      }

      return new CustomExtension;
      """
    And a file named "custom_extension2.php" with:
      """
      <?php

      use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
      use Symfony\Component\DependencyInjection\ContainerBuilder;

      class CustomInitializer2 implements Behat\Behat\Context\Initializer\ContextInitializer
      {
          public function supportsContext(Behat\Behat\Context\Context $context)
          {
              return true;
          }

          public function initializeContext(Behat\Behat\Context\Context $context)
          {
              $context->addExtension('custom extension 2');
          }
      }

      class CustomExtension2 implements Behat\Testwork\ServiceContainer\Extension {
          public function getConfigKey()
          {
              return 'custom_extension2';
          }

          public function configure(ArrayNodeDefinition $builder) {}

          public function initialize(Behat\Testwork\ServiceContainer\ExtensionManager $extensionManager) {}

          public function load(ContainerBuilder $container, array $config)
          {
              $definition = $container->register('custom_initializer2', 'CustomInitializer2');
              $definition->addTag('context.initializer', array('priority' => 100));
          }

          public function process(ContainerBuilder $container) {}
      }

      return new CustomExtension2;
      """
    And a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          private $loadedExtensions = array();

          public function addExtension($extensionName) {
              $this->loadedExtensions[] = $extensionName;
          }

          /** @When this scenario executes */
          public function thisScenarioExecutes() {}

          /** @Then the extension :name should be loaded */
          public function theExtensionLoaded($name) {
              PHPUnit\Framework\Assert::assertContains($name, $this->loadedExtensions);
          }

          /** @Then the extension :name should not be loaded */
          public function theExtensionNotLoaded($name) {
              PHPUnit\Framework\Assert::assertNotContains($name, $this->loadedExtensions);
          }

          /** @Then the extension :name should have :key set to :value */
          public function theExtensionHas($name, $key, $value) {
              $this->theExtensionLoaded($name);
              $this->theExtensionNotLoaded($name);
          }
      }
      """

  Scenario: Default profile has extension loaded
    Given a file named "behat.yml.dist" with:
      """
      default:
        extensions:
          custom_extension.php: ~

      custom_profile:
        extensions: ~
      """
    And a file named "features/extensions.feature" with:
      """
      Feature:
        Scenario:
          When this scenario executes
          Then the extension "custom extension 1" should be loaded
      """
    When I run "behat -f progress"
    Then it should pass

  Scenario: Custom profile disables all extensions
    Given a file named "behat.yml.dist" with:
      """
      default:
        extensions:
          custom_extension.php: ~

      custom_profile:
        extensions: ~
      """
    And a file named "features/extensions.feature" with:
      """
      Feature:
        Scenario:
          When this scenario executes
          Then the extension "custom extension 1" should not be loaded
      """
    When I run "behat -f progress -p custom_profile -vvv"
    Then it should pass

  Scenario: Custom profile has an additional extension
    Given a file named "behat.yml.dist" with:
      """
      default:
        extensions:
          custom_extension.php: ~

      custom_profile:
        extensions:
          custom_extension2.php: ~
      """
    And a file named "features/extensions.feature" with:
      """
      Feature:
        Scenario:
          When this scenario executes
          Then the extension "custom extension 1" should be loaded
          And the extension "custom extension 2" should be loaded
      """
    When I run "behat -f progress -p custom_profile -vvv"
    Then it should pass
