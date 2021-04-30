Feature: attributes
  In order to keep annotations shorter and faster to parse
  As a tester
  I need to be able to use PHP8 Attributes

  @php8
  Scenario:
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Step\Given, Behat\Step\When, Behat\Step\Then;

      class FeatureContext implements \Behat\Behat\Context\Context
      {
          #[Given('I have :count apple(s)')]
          #[Given('I have :count banana(s)')]
          public function iHaveFruit($count) { }

          #[When('I eat :count apple(s)')]
          #[When('I eat :count banana(s)')]
          public function iEatFruit($count) { }

          #[Then('I should have :count apple(s)')]
          #[Then('I should have :count banana(s)')]
          public function iShouldHaveFruit($count) { }
      }
      """
    And a file named "features/some.feature" with:
      """
      Feature: Fruit story
        In order to eat fruit
        As a little kid
        I need to have fruit in my pocket

        Scenario: I'm little hungry for apples
          Given I have 3 apples
          When I eat 1 apple
          Then I should have 2 apples

        Scenario: I'm little hungry for bananas
          Given I have 3 bananas
          When I eat 1 banana
          Then I should have 2 bananas
      """
    When I run "behat --no-colors -fpretty --format-settings='{\"paths\": true}' features"
    Then it should pass with:
      """
      Feature: Fruit story
        In order to eat fruit
        As a little kid
        I need to have fruit in my pocket

        Scenario: I'm little hungry for apples # features/some.feature:6
          Given I have 3 apples                # FeatureContext::iHaveFruit()
          When I eat 1 apple                   # FeatureContext::iEatFruit()
          Then I should have 2 apples          # FeatureContext::iShouldHaveFruit()

        Scenario: I'm little hungry for bananas # features/some.feature:11
          Given I have 3 bananas                # FeatureContext::iHaveFruit()
          When I eat 1 banana                   # FeatureContext::iEatFruit()
          Then I should have 2 bananas          # FeatureContext::iShouldHaveFruit()

      2 scenarios (2 passed)
      6 steps (6 passed)
      """
