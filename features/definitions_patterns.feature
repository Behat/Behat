Feature: Step Definition Pattern
  In order to fix my mistakes easily
  As a step definitions developer
  I need to be able to use complex and weird patterns

  Scenario: Pattern with token at the start of the step
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          /**
           * @Then :token should be :value
           */
          public function shouldBe($token, $value) {
          }
      }
      """
    And a file named "features/step_patterns.feature" with:
      """
      Feature: Step Pattern
        Scenario:
          Then 5 should be 10
          Then "foo" should be "bar"
      """
    When I run "behat -f progress --no-colors"
    Then it should pass with:
      """
      ..

      1 scenario (1 passed)
      2 steps (2 passed)
      """

  Scenario: Pattern with broken regex
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          /**
           * @Then /I am (foo/
           */
          public function invalidRegex() {
          }
      }
      """
    And a file named "features/step_patterns.feature" with:
      """
      Feature: Step Pattern
        Scenario:
          Then I am foo
      """
    When I run "behat -f progress --no-colors"
    Then it should fail with:
      """
      [Behat\Behat\Definition\Exception\InvalidPatternException]
        The regex `/I am (foo/` is invalid:
      """
