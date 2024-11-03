Feature: Strict type defined in configuration file
  As a feature writer
  I need to be able to configure "strict" option in behat.yml config file

  Scenario: Undefined steps
    Given a file named "features/coffee.feature" with:
      """
      Feature: Undefined coffee machine actions
        In order to make clients happy
        As a coffee machine factory
        We need to be able to tell customers
        about what coffee type is supported

        Background:
          Given I have magically created 10$

        Scenario: Buy incredible coffee
          When I have chose "coffee with turkey" in coffee machine
          Then I should have "turkey with coffee sauce"

        Scenario: Buy incredible tea
          When I have chose "pizza tea" in coffee machine
          Then I should have "pizza tea"
      """
    And a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context,
          Behat\Behat\Tester\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeatureContext implements Context {}
      """
    And a file named "behat.yml" with:
      """
      default:
        config:
          strict: false
      """
    When I run "behat --no-colors -f progress features/coffee.feature"
    Then it should pass with:
      """
      UUUUUU

      2 scenarios (2 undefined)
      6 steps (6 undefined)
      """

    Given a file named "behat.yml" with:
      """
      default:
        config:
          strict: true
      """
    When I run "behat --no-colors -f progress features/coffee.feature"
    Then it should fail with:
      """
      UUUUUU

      2 scenarios (2 undefined)
      6 steps (6 undefined)

      """
