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

      require_once 'PHPUnit/Autoload.php';
      require_once 'PHPUnit/Framework/Assert/Functions.php';

      use Behat\Behat\Context\BehatContext;

      class FeatureContext extends BehatContext
      {
          private $extension;

          public function setExtensionParameters(array $parameters) {
              $this->extension = $parameters;
          }

          /** @When this scenario executes */
          public function thisScenarioExecutes() {}

          /** @Then the extension should be loaded */
          public function theExtensionLoaded() {
              assertEquals(array('param1' => 'val1', 'param2' => 'val2'), $this->extension);
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

      class CustomExtension extends Behat\Behat\Extension\Extension {}

      return new CustomExtension;
      """
    And a file named "CustomExtensionServices.yml" with:
      """
      services:
        custom_initializer:
          class: CustomInitializer
          arguments:
            - %custom_extension.parameters%
          tags:
            - { name: behat.context.initializer }
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
