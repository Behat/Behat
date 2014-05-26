Feature: Config parameters
  In order to reuse context parameters
  As a context developer
  I need to be able to specify global parameters in config file

  Background:
    Given a file named "behat.yml" with:
    """
    default:
      suites:
        default:
          contexts:
            - FeatureContext: [ %param1% ]
    """
    And a file named "behat_parameters.yml" with:
    """
    parameters:
      param1: val1
    """
    And a file named "features/bootstrap/FeatureContext.php" with:
    """
    <?php

    use Behat\Behat\Context\Context;

    class FeatureContext implements Context
    {
        private $param1;

        public function __construct($param1) {
            $this->param1 = $param1;
        }

        /** @When this scenario executes */
        public function thisScenarioExecutes() {}

        /** @Then the context parameter should be set to global parameter */
        public function theContextParametersOverwrite() {
            \PHPUnit_Framework_Assert::assertEquals('val1', $this->param1);
        }
    }
    """
    And a file named "features/configs.feature" with:
    """
    Feature:
      Scenario:
        When this scenario executes
        Then the context parameter should be set to global parameter
    """

  Scenario: Config should successfully set the parameter to context
    When I run "behat -f progress --append-snippets"
    Then it should pass
