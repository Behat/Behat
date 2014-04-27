Feature: Step Definition Pattern
  In order to fix my mistakes easily
  As a step definitions developer
  I need to be told when a pattern is invalid

  Scenario: Simple Arguments Transformations
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
