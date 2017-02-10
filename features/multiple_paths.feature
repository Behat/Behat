Feature: Multiple Paths
  In order to run only needed features
  As a Behat user
  I need Behat to support multiple paths

  Background:
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      class FeatureContext implements \Behat\Behat\Context\Context
      {
          /**
           * @Given /^I have (\d+) apples?$/
           */
          public function iHaveApples($count) {
              $this->apples = intval($count);
          }

          /**
           * @When /^I ate (\d+) apples?$/
           */
          public function iAteApples($count) {
              $this->apples -= intval($count);
          }

          /**
           * @When /^I found (\d+) apples?$/
           */
          public function iFoundApples($count) {
              $this->apples += intval($count);
          }

          /**
           * @Then /^I should have (\d+) apples$/
           */
          public function iShouldHaveApples($count) {
              PHPUnit_Framework_Assert::assertEquals(intval($count), $this->apples);
          }
      }
      """
    And there is a file named "features/apples.feature" with:
    """
    Feature: Apples story
      In order to eat apple
      As a little kid
      I need to have an apple in my pocket

      Background:
        Given I have 3 apples

      Scenario: I'm little hungry
        When I ate 1 apple
        Then I should have 2 apples

      Scenario: Found more apples
        When I found 5 apples
        Then I should have 8 apples
    """
    And there is a file named "features/more_apples.feature" with:
    """
    Feature: Another Apples story
      In order to eat apple
      As a little kid
      I need to have an apple in my pocket

      Background:
        Given I have 3 apples

      Scenario: I'm little hungry
        When I ate 2 apple
        Then I should have 1 apples

      Scenario: Found more apples
        When I found 4 apples
        Then I should have 7 apples
    """

  Scenario: 2 features
    When I run "behat --no-colors -f progress features/apples.feature features/more_apples.feature"
    Then it should pass with:
    """
    4 scenarios (4 passed)
    """

  Scenario: No Features
    When I run "behat --no-colors -f progress features/pears.feature features/peaches.feature"
    Then it should fail with:
    """
    No specifications found at path(s) `features/pears.feature, features/peaches.feature`
    """

