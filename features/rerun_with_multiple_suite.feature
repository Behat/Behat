Feature: Rerun with multiple suite
  In order to test only failed scenarios in different suite
  As a feature developer
  I need to have an ability to rerun failed previously scenarios

  Background:
    Given a file named "behat.yml" with:
      """
      default:
        suites:
          default:
            paths:
              - features/apples.feature
              - features/bananas.feature
            contexts:
              - FeatureContext: [ { param1: val1, param2: val1 } ]
          suite1:
            paths: {}
          suite2:
            paths:
              - features/bananas.feature
      """
    And a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          private $fruits = array();
          private $parameters;

          public function __construct(array $parameters = array()) {
              $this->parameters = $parameters;
          }

          /**
           * @Given /^I have (\d+) (apples|bananas)$/
           */
          public function iHaveFruit($count, $fruit) {
              $this->fruits[$fruit] = intval($count);
          }

          /**
           * @When /^I ate (\d+) (apples|bananas)$/
           */
          public function iAteFruit($count, $fruit) {
              $this->fruits[$fruit] -= intval($count);
          }

          /**
           * @When /^I found (\d+) (apples|bananas)$/
           */
          public function iFoundFruit($count, $fruit) {
              $this->fruits[$fruit] += intval($count);
          }

          /**
           * @Then /^I should have (\d+) (apples|bananas)$/
           */
          public function iShouldHaveFruit($count, $fruit) {
              PHPUnit\Framework\Assert::assertEquals(intval($count), $this->fruits[$fruit]);
          }

          /**
           * @Then /^context parameter "([^"]*)" should be equal to "([^"]*)"$/
           */
          public function contextParameterShouldBeEqualTo($key, $val) {
              PHPUnit\Framework\Assert::assertEquals($val, $this->parameters[$key]);
          }

          /**
           * @Given /^context parameter "([^"]*)" should be array with (\d+) elements$/
           */
          public function contextParameterShouldBeArrayWithElements($key, $count) {
              PHPUnit\Framework\Assert::assertIsArray($this->parameters[$key]);
              PHPUnit\Framework\Assert::assertEquals(2, count($this->parameters[$key]));
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
          When I ate 1 apples
          Then I should have 3 apples

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
            | 0   | 4     | 8      |
            | 2   | 2     | 3      |
      """
    And a file named "features/bananas.feature" with:
      """
      Feature: Banana story
        In order to eat banana
        As a little kid
        I need to have an banana in my pocket

        Background:
          Given I have 3 bananas

        Scenario: I'm little hungry
          When I ate 1 bananas
          Then I should have 3 bananas

        Scenario: Found more bananas
          When I found 5 bananas
          Then I should have 8 bananas

        Scenario: Found more bananas
          When I found 2 bananas
          Then I should have 5 bananas

        Scenario Outline: Other situations
          When I ate <ate> bananas
          And I found <found> bananas
          Then I should have <result> bananas

          Examples:
            | ate | found | result |
            | 3   | 1     | 1      |
            | 0   | 4     | 8      |
            | 2   | 2     | 3      |
      """

  Scenario: Run feature with 6 failed and 12 passing scenarios
    When I run "behat --no-colors -f progress"
    Then it should fail with:
      """
      ..F.............F......F.............F......F.............F....

      --- Failed steps:

      001 Scenario: I'm little hungry   # features/apples.feature:9
            Then I should have 3 apples # features/apples.feature:11
              Failed asserting that 2 matches expected 3.

      002 Example: | 0   | 4     | 8      | # features/apples.feature:29
            Then I should have 8 apples     # features/apples.feature:24
              Failed asserting that 7 matches expected 8.

      003 Scenario: I'm little hungry    # features/bananas.feature:9
            Then I should have 3 bananas # features/bananas.feature:11
              Failed asserting that 2 matches expected 3.

      004 Example: | 0   | 4     | 8      | # features/bananas.feature:29
            Then I should have 8 bananas    # features/bananas.feature:24
              Failed asserting that 7 matches expected 8.

      005 Scenario: I'm little hungry    # features/bananas.feature:9
            Then I should have 3 bananas # features/bananas.feature:11
              Failed asserting that 2 matches expected 3.

      006 Example: | 0   | 4     | 8      | # features/bananas.feature:29
            Then I should have 8 bananas    # features/bananas.feature:24
              Failed asserting that 7 matches expected 8.

      18 scenarios (12 passed, 6 failed)
      63 steps (57 passed, 6 failed)
      """

  Scenario: Rerun only failed scenarios, 4 from default suite and 2 from suite2
    Given I run "behat --no-colors -f progress"
    When I run "behat --no-colors -f progress --rerun"
    Then it should fail with:
    """
    ..F...F..F...F..F...F

    --- Failed steps:

    001 Scenario: I'm little hungry   # features/apples.feature:9
          Then I should have 3 apples # features/apples.feature:11
            Failed asserting that 2 matches expected 3.

    002 Example: | 0   | 4     | 8      | # features/apples.feature:29
          Then I should have 8 apples     # features/apples.feature:24
            Failed asserting that 7 matches expected 8.

    003 Scenario: I'm little hungry    # features/bananas.feature:9
          Then I should have 3 bananas # features/bananas.feature:11
            Failed asserting that 2 matches expected 3.

    004 Example: | 0   | 4     | 8      | # features/bananas.feature:29
          Then I should have 8 bananas    # features/bananas.feature:24
            Failed asserting that 7 matches expected 8.

    005 Scenario: I'm little hungry    # features/bananas.feature:9
          Then I should have 3 bananas # features/bananas.feature:11
            Failed asserting that 2 matches expected 3.

    006 Example: | 0   | 4     | 8      | # features/bananas.feature:29
          Then I should have 8 bananas    # features/bananas.feature:24
            Failed asserting that 7 matches expected 8.

    6 scenarios (6 failed)
    21 steps (15 passed, 6 failed)
    """

  Scenario: Fixing scenarios removes it from the rerun log
    Given I run "behat --no-colors -f progress features/apples.feature"
    And there is a file named "features/bananas.feature" with:
      """
      Feature: Banana story
        In order to eat banana
        As a little kid
        I need to have an banana in my pocket

        Background:
          Given I have 3 bananas

        Scenario: I'm little hungry
          When I ate 1 bananas
          Then I should have 2 bananas

        Scenario: Found more bananas
          When I found 5 bananas
          Then I should have 8 bananas

        Scenario: Found more bananas
          When I found 2 bananas
          Then I should have 5 bananas

        Scenario Outline: Other situations
          When I ate <ate> bananas
          And I found <found> bananas
          Then I should have <result> bananas

          Examples:
            | ate | found | result |
            | 3   | 1     | 1      |
            | 0   | 4     | 7      |
            | 2   | 2     | 3      |
      """
    When I run "behat --no-colors -f progress"
    And I run "behat --no-colors -f progress --rerun"
    Then it should fail with:
    """
    ..F...F

    --- Failed steps:

    001 Scenario: I'm little hungry   # features/apples.feature:9
          Then I should have 3 apples # features/apples.feature:11
            Failed asserting that 2 matches expected 3.

    002 Example: | 0   | 4     | 8      | # features/apples.feature:29
          Then I should have 8 apples     # features/apples.feature:24
            Failed asserting that 7 matches expected 8.

    2 scenarios (2 failed)
    7 steps (5 passed, 2 failed)
    """

  Scenario: Rerun only suite failed scenarios from suite2 suite
    Given I run "behat --no-colors -f progress --suite suite2"
    When I run "behat --no-colors -f progress --suite suite2 --rerun"
    Then it should fail with:
    """
    ..F...F

    --- Failed steps:

    001 Scenario: I'm little hungry    # features/bananas.feature:9
          Then I should have 3 bananas # features/bananas.feature:11
            Failed asserting that 2 matches expected 3.

    002 Example: | 0   | 4     | 8      | # features/bananas.feature:29
          Then I should have 8 bananas    # features/bananas.feature:24
            Failed asserting that 7 matches expected 8.

    2 scenarios (2 failed)
    7 steps (5 passed, 2 failed)
    """
