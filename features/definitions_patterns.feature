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

  Scenario: Pattern with decimal point
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          /**
           * @Then :token should have value of £:value
           */
          public function shouldHaveValueOf($token, $value) {
            echo $value;
          }
      }
      """
    And a file named "features/step_patterns.feature" with:
      """
      Feature: Step Pattern
        Scenario:
          Then 5 should have value of £10
          And 7 should have value of £7.2
      """
    When I run "behat -f pretty --no-colors"
    Then it should pass with:
      """
      Feature: Step Pattern

        Scenario:                         # features/step_patterns.feature:2
          Then 5 should have value of £10 # FeatureContext::shouldHaveValueOf()
            │ 10
          And 7 should have value of £7.2 # FeatureContext::shouldHaveValueOf()
            │ 7.2

      1 scenario (1 passed)
      2 steps (2 passed)
      """

  Scenario: Pattern with string including point
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          /**
           * @Then :token should have value of :first.:second
           */
          public function shouldHaveValueOf($token, $first, $second) {
            echo $first . ' + ' . $second;
          }
      }
      """
    And a file named "features/step_patterns.feature" with:
      """
      Feature: Step Pattern
        Scenario:
          Then 5 should have value of two.three
          And 7 should have value of three.4
          And 7 should have value of 3.four
      """
    When I run "behat -f pretty --no-colors"
    Then it should pass with:
      """
      Feature: Step Pattern

        Scenario:                               # features/step_patterns.feature:2
          Then 5 should have value of two.three # FeatureContext::shouldHaveValueOf()
            │ two + three
          And 7 should have value of three.4    # FeatureContext::shouldHaveValueOf()
            │ three + 4
          And 7 should have value of 3.four     # FeatureContext::shouldHaveValueOf()
            │ 3 + four

      1 scenario (1 passed)
      3 steps (3 passed)
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

  Scenario: Pattern with default values
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          /**
           * @Given only second :second
           */
          public function invalidRegex($first = 'foo', $second = 'fiz') {
            PHPUnit_Framework_Assert::assertEquals('foo', $first);
            PHPUnit_Framework_Assert::assertEquals('bar', $second);
          }
      }
      """
    And a file named "features/step_patterns.feature" with:
      """
      Feature: Step Pattern
        Scenario:
          Given only second "bar"
      """
    When I run "behat -f progress --no-colors"
    Then it should pass with:
      """
      .

      1 scenario (1 passed)
      1 step (1 passed)
      """
