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
            param2: val2
        extensions:
          custom_extension.php:
            param1: val2

      custom_profile:
        suites:
          default:
            param2: val2
        extensions:
          custom_extension.php:
            param1: val2
      """
    Given a file named "behat.yml.dist" with:
      """
      default:
        suites:
          default:
            param1: val1
            param2: val1
        extensions:
          custom_extension.php:
            param1: val1
            param2: val1

      custom_profile:
        suites:
          default:
            param1: val1
            param2: val1
        extensions:
          custom_extension.php:
            param1: val1
            param2: val1
      """
    And a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\ContextInterface;

      class FeatureContext implements ContextInterface
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

          /** @Then the context config should be merged */
          public function theContextConfigMerged() {
              \PHPUnit_Framework_Assert::assertEquals('val1', $this->parameters['param1']);
              \PHPUnit_Framework_Assert::assertEquals('val2', $this->parameters['param2']);
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
      use Behat\Behat\Context\ContextInterface;

      class CustomInitializer implements Behat\Behat\Context\Initializer\InitializerInterface
      {
          private $parameters;

          public function __construct(array $parameters)
          {
              $this->parameters = $parameters;
          }

          public function supports(ContextInterface $context)
          {
              return true;
          }

          public function initialize(ContextInterface $context)
          {
              $context->setExtensionParameters($this->parameters);
          }
      }

      class CustomExtension extends Behat\Behat\Extension\Extension
      {
          public function getExtensionName() {
              return 'custom_extension';
          }

          public function load(array $config, ContainerBuilder $container) {
              $definition = $container->register('custom_initializer', 'CustomInitializer');
              $definition->setArguments(array($config));
              $definition->addTag('context.initializer');
          }
      }

      return new CustomExtension;
      """
    And a file named "features/configs.feature" with:
      """
      Feature:
        Scenario:
          When this scenario executes
          Then the context config should be merged
          And the extension config should be merged
      """

  Scenario: Config should successfully inherit parent one for default profiles
    When I run "behat -f progress --append-snippets"
    Then it should pass

  Scenario: Config should successfully inherit parent one for custom profiles
    When I run "behat -f progress --append-snippets --profile custom_profile"
    Then it should pass
