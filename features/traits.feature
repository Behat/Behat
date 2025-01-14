Feature: Support traits
  In order to have much cleaner horizontal reusability
  As a context developer
  I need to be able to use definition traits in my context

  Background:
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          use ApplesDefinitions;
      }
      """
    And a file named "features/bootstrap/ApplesDefinitions.php" with:
      """
      <?php

      use Behat\Step\Given;
      use Behat\Step\Then;
      use Behat\Step\When;

      trait ApplesDefinitions
      {
          private $apples = 0;

          #[Given('/^I have (\d+) apples?$/')]
          public function iHaveApples($count) {
              $this->apples = intval($count);
          }

          #[When('/^I ate (\d+) apples?$/')]
          public function iAteApples($count) {
              $this->apples -= intval($count);
          }

          #[When('/^I found (\d+) apples?$/')]
          public function iFoundApples($count) {
              $this->apples += intval($count);
          }

          #[Then('/^I should have (\d+) apples$/')]
          public function iShouldHaveApples($count) {
              PHPUnit\Framework\Assert::assertEquals(intval($count), $this->apples);
          }
      }
      """
    And a file named "features/apples.feature" with:
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

        Scenario: Found more apples
          When I found 2 apples
          Then I should have 5 apples

        Scenario Outline: Other situations
          When I ate <ate> apples
          And I found <found> apples
          Then I should have <result> apples

          Examples:
            | ate | found | result |
            | 3   | 1     | 1      |
            | 0   | 4     | 7      |
            | 2   | 2     | 3      |
      """

  Scenario: Run feature with failing scenarios
    When I run "behat --no-colors -f progress"
    Then it should pass with:
      """
      .....................

      6 scenarios (6 passed)
      21 steps (21 passed)
      """
