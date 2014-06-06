Feature: Step Definitions Override
  In order to fine-tune definitions defined in parent classes
  As a step definitions developer
  I need to be able to override definition methods

  Scenario: Overriden method without own annotation will inherit parent pattern
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class ParentContext
      {
          /**
           * @Then :token should be :value
           */
          public function shouldBe($token, $value) {}
      }

      class FeatureContext extends ParentContext implements Context
      {
          public function shouldBe($token, $value) {}
      }
      """
    And a file named "features/step_patterns.feature" with:
      """
      Feature: Step Pattern
        Scenario:
          Then 5 should be 10
      """
    When I run "behat -f progress --no-colors"
    Then it should pass with:
      """
      .

      1 scenario (1 passed)
      1 step (1 passed)
      """

  Scenario: Overriden method with different annotation will have both patterns
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class ParentContext
      {
          /**
           * @Then :token should be :value
           */
          public function shouldBe($token, $value) {}
      }

      class FeatureContext extends ParentContext implements Context
      {
          /**
           * @Then :token should be equal to :value
           */
          public function shouldBe($token, $value) {}
      }
      """
    And a file named "features/step_patterns.feature" with:
      """
      Feature: Step Pattern
        Scenario:
          Then 5 should be equal to 10
          Then 5 should be 10
      """
    When I run "behat -f progress --no-colors"
    Then it should pass with:
      """
      ..

      1 scenario (1 passed)
      2 steps (2 passed)
      """
