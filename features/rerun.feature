Feature: Rerun
  In order to test only failed scenarios
  As a feature developer
  I need to have an ability to rerun failed previously scenarios

  Background:
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          private $apples = 0;
          private $parameters;

          public function __construct(array $parameters = array()) {
              $this->parameters = $parameters;
          }

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

          /**
           * @Then /^context parameter "([^"]*)" should be equal to "([^"]*)"$/
           */
          public function contextParameterShouldBeEqualTo($key, $val) {
              PHPUnit_Framework_Assert::assertEquals($val, $this->parameters[$key]);
          }

          /**
           * @Given /^context parameter "([^"]*)" should be array with (\d+) elements$/
           */
          public function contextParameterShouldBeArrayWithElements($key, $count) {
              PHPUnit_Framework_Assert::assertInternalType('array', $this->parameters[$key]);
              PHPUnit_Framework_Assert::assertEquals(2, count($this->parameters[$key]));
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

  Scenario: Run one feature with 2 failed and 3 passing scenarios
    When I run "behat --no-colors -f progress features/apples.feature"
    Then it should fail with:
      """
      ..F.............F....

      --- Failed steps:

          Then I should have 3 apples # features/apples.feature:11
            Failed asserting that 2 matches expected 3.

          Then I should have 8 apples # features/apples.feature:24
            Failed asserting that 7 matches expected 8.

      6 scenarios (4 passed, 2 failed)
      21 steps (19 passed, 2 failed)
      """

  Scenario: Rerun only failed scenarios
    Given I run "behat --no-colors -f progress features/apples.feature"
    When I run "behat --no-colors -f progress features/apples.feature --rerun"
    Then it should fail with:
    """
    ..F...F

    --- Failed steps:

        Then I should have 3 apples # features/apples.feature:11
          Failed asserting that 2 matches expected 3.

        Then I should have 8 apples # features/apples.feature:24
          Failed asserting that 7 matches expected 8.

    2 scenarios (2 failed)
    7 steps (5 passed, 2 failed)
    """

  Scenario: Fixing scenario removes it from the rerun log
    Given I run "behat --no-colors -f progress features/apples.feature"
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
            | 0   | 4     | 7      |
            | 2   | 2     | 3      |
      """
    When I run "behat --no-colors -f progress features/apples.feature"
    And I run "behat --no-colors -f progress features/apples.feature --rerun"
    Then it should fail with:
    """
    ..F

    --- Failed steps:

        Then I should have 3 apples # features/apples.feature:11
          Failed asserting that 2 matches expected 3.

    1 scenario (1 failed)
    3 steps (2 passed, 1 failed)
    """
