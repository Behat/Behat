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

      use Behat\Behat\Context\ContextInterface;

      class FeatureContext implements ContextInterface
      {
          private $extension;

          public function setExtensionParameters(array $parameters) {
              $this->extension = $parameters;var_dump($parameters);
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

      class CustomInitializer implements Behat\Behat\Context\Initializer\InitializerInterface
      {
          private $parameters;

          public function __construct(array $parameters)
          {
              $this->parameters = $parameters;
          }

          public function supports(Behat\Behat\Context\ContextInterface $context)
          {
              return true;
          }

          public function initialize(Behat\Behat\Context\ContextInterface $context)
          {
              $context->setExtensionParameters($this->parameters);
          }
      }

      class CustomExtension extends Behat\Behat\Extension\Extension {
          public function getName() {
              return 'custom_extension';
          }
      }

      return new CustomExtension;
      """
    And a file named "services.yml" with:
      """
      services:
        custom_initializer:
          class: CustomInitializer
          arguments:
            - %custom_extension.parameters%
          tags:
            - { name: context.initializer }
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
