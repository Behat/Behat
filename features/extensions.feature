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
              PHPUnit\Framework\Assert::assertEquals(array('param1' => 'val1', 'param2' => 'val2'), $this->extension);
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
      `inexistent_extension` extension file or class could not be located.
      """

  Scenario: Exception handlers extension
    Given a file named "behat.yml" with:
      """
      default:
        extensions:
          custom_extension.php: ~
      """
    And a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          /** @Given non-existent class */
          public function nonexistentClass() {
              $ins = new Non\Existent\Cls();
          }

          /** @Given non-existent method */
          public function nonexistentMethod() {
              $this->getName();
          }
      }
      """
    And a file named "custom_extension.php" with:
      """
      <?php

      use Behat\Testwork\ServiceContainer\Extension;
      use Behat\Testwork\ServiceContainer\ExtensionManager;
      use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
      use Symfony\Component\DependencyInjection\Definition;
      use Symfony\Component\DependencyInjection\ContainerBuilder;
      use Behat\Testwork\Call\ServiceContainer\CallExtension;

      class NonExistentClassPrinter extends Behat\Testwork\Call\Handler\Exception\ClassNotFoundHandler
      {
          public function handleNonExistentClass($class) { var_dump($class); }
      }

      class NonExistentMethodPrinter extends Behat\Testwork\Call\Handler\Exception\MethodNotFoundHandler
      {
          public function handleNonExistentMethod(array $callable) { var_dump($callable); }
      }

      class CustomHandlers implements Extension {
          public function getConfigKey() { return 'custom_handlers'; }
          public function configure(ArrayNodeDefinition $builder) { }
          public function initialize(Behat\Testwork\ServiceContainer\ExtensionManager $extensionManager) {}
          public function process(ContainerBuilder $container) {}

          public function load(ContainerBuilder $container, array $config)
          {
              $definition = new Definition('NonExistentClassPrinter', array());
              $definition->addTag(CallExtension::EXCEPTION_HANDLER_TAG, array('priority' => 50));
              $container->setDefinition(CallExtension::EXCEPTION_HANDLER_TAG . '.class_printer', $definition);

              $definition = new Definition('NonExistentMethodPrinter', array());
              $definition->addTag(CallExtension::EXCEPTION_HANDLER_TAG, array('priority' => 55));
              $container->setDefinition(CallExtension::EXCEPTION_HANDLER_TAG . '.method_printer', $definition);
          }
      }

      return new CustomHandlers;
      """
    And a file named "features/extensions.feature" with:
      """
      Feature:
        Scenario:
          Given non-existent class
        Scenario:
          Given non-existent method
      """
    When I run "behat -f progress --no-colors"
    Then it should fail with:
      """
      FF

      --- Failed steps:

      001 Scenario:                  # features/extensions.feature:2
            Given non-existent class # features/extensions.feature:3
              Fatal error: Class 'Non\Existent\Cls' not found (Behat\Testwork\Call\Exception\FatalThrowableError)

      002 Scenario:                   # features/extensions.feature:4
            Given non-existent method # features/extensions.feature:5
              Fatal error: Call to undefined method FeatureContext::getName() (Behat\Testwork\Call\Exception\FatalThrowableError)

      2 scenarios (2 failed)
      2 steps (2 failed)
      string(16) "Non\Existent\Cls"
      array(2) {
        [0]=>
        string(14) "FeatureContext"
        [1]=>
        string(7) "getName"
      }
      """
