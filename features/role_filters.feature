Feature: Role filters
  In order to run only needed features
  As a Behat user
  I need Behat to support features filtering based on role

  Scenario: Brothers
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php
      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          /** @Given I have :count apple(s) */
          public function iHaveApples($count) { }

          /** @When I ate :count apple(s) */
          public function iAteApples($count) { }

          /** @Then I should have :count apple(s) */
          public function iShouldHaveApples($count) { }
      }
      """
    And a file named "features/little_kid.feature" with:
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
    And a file named "features/big_brother.feature" with:
      """
      Feature: Apples story
        In order to eat apple
        As a big brother
        I need to have an apple in my pocket

        Scenario: I'm little hungry
          Given I have 15 apples
          When I ate 10 apple
          Then I should have 5 apples
      """
    When I run "behat --no-colors -f pretty --role 'little kid'"
    Then it should pass with:
      """
      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Scenario: I'm little hungry   # features/little_kid.feature:6
          Given I have 3 apples       # FeatureContext::iHaveApples()
          When I ate 1 apple          # FeatureContext::iAteApples()
          Then I should have 2 apples # FeatureContext::iShouldHaveApples()

      1 scenario (1 passed)
      3 steps (3 passed)
      """
