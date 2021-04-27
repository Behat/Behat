Feature: attributes
  In order to keep annotations shorter and faster to parse
  As a tester
  I need to be able to use PHP8 Attributes

  @php8
  Scenario:
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use \Behat\Behat\Definition\Attribute\Given, \Behat\Behat\Definition\Attribute\When,
        \Behat\Behat\Definition\Attribute\Then;

      class FeatureContext implements \Behat\Behat\Context\Context
      {
          #[Given('I have :count apple(s)')]
          public function iHaveApples($count) { }

          #[When('I ate :count apple(s)')]
          public function iAteApples($count) { }

          #[Then('I should have :count apple(s)')]
          public function iShouldHaveApples($count) { }
      }
      """
    And a file named "features/some.feature" with:
      """
      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Scenario: I'm little hungry
          Given I have 3 apples
          When I ate 1 apple
          Then I should have 2 apples
      """
    When I run "behat --no-colors -fpretty --format-settings='{\"paths\": true}' features"
    Then it should pass with:
      """
      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Scenario: I'm little hungry   # features/some.feature:6
          Given I have 3 apples       # FeatureContext::iHaveApples()
          When I ate 1 apple          # FeatureContext::iAteApples()
          Then I should have 2 apples # FeatureContext::iShouldHaveApples()

      1 scenario (1 passed)
      3 steps (3 passed)
      """
