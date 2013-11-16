Feature: JUnit Formatter
  In order to print features
  As a feature writer
  I need to have an junit formatter

  Background:
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\ContextInterface,
          Behat\Behat\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeatureContext implements ContextInterface
      {
          private $value = 0;

          /**
           * @Given /I have entered (\d+)/
           */
          public function iHaveEntered($number) {
              $this->value = $number;
          }

          /**
           * @Then /I must have (\d+)/
           */
          public function iMustHave($number) {
              PHPUnit_Framework_Assert::assertEquals($number, $this->value);
          }

          /**
           * @When /I (add|subtract) the value (\d+)/
           */
          public function iAddOrSubstractValue($operation, $number) {
              switch ($operation) {
                  case 'add':
                      $this->value += $number;
                      break;
                  case 'subtract':
                      $this->value -= $number;
                      break;
              }
          }
      }
      """

  Scenario: Multiple parameters
    Given a file named "features/World.feature" with:
      """
      Feature: World consistency
        In order to maintain stable behaviors
        As a features developer
        I want, that "World" flushes between scenarios

        Background:
          Given I have entered 10

        Scenario: Adding
          Then I must have 10
          And I add the value 6
          Then I must have 16

        Scenario: Subtracting
          Then I must have 10
          And I subtract the value 6
          Then I must have 4
      """
    When I run "behat --no-ansi -f junit --out /tmp/test/behat"
    Then it should pass
    And the junit file "/tmp/test/behat/TEST-World.xml" should contain:
      """
      <?xml version="1.0" encoding="UTF-8"?>
      <testsuite errors="0" failures="0" skipped="0" name="World consistency" tests="2" time="XXX">
          <testcase classname="World consistency" name="Adding" time="XXX" assertions="4">
          </testcase>
          <testcase classname="World consistency" name="Subtracting" time="XXX" assertions="4">
          </testcase>
      </testsuite>

      """

  Scenario: Multiple parameters
    Given a file named "features/World-Error.feature" with:
      """
      Feature: World consistency
        In order to maintain stable behaviors
        As a features developer
        I want, that "World" flushes between scenarios

        Background:
          Given I have entered 10

        Scenario: Adding
          Then I must have 10
          Then I must have 15

        Scenario: Subtracting
          Then I must have 10
          And I subtract the value 6
          Then I must have 4
      """
    When I run "behat --no-ansi -f junit --out /tmp/test/behat"
    Then it should fail
    And the junit file "/tmp/test/behat/TEST-World-Error.xml" should contain:
      """
      <?xml version="1.0" encoding="UTF-8"?>
      <testsuite errors="0" failures="1" skipped="0" name="World consistency" tests="2" time="XXX">
          <testcase classname="World consistency" name="Adding" time="XXX" assertions="3">
              <failure message="Failed asserting that '10' matches expected '15'." type="failed"><![CDATA[XXX]]></failure>
          </testcase>
          <testcase classname="World consistency" name="Subtracting" time="XXX" assertions="4">
          </testcase>
      </testsuite>

      """

  Scenario: Multiple parameters
    Given a file named "features/World.feature" with:
    """
      Feature: World consistency
        In order to maintain stable behaviors
        As a features developer
        I want, that "World" flushes between scenarios

        Background:
          Given I have entered 10

        Scenario: Adding
          Then I must have 10
          And I add the value 6
          Then I must have 16
      """
    And I am in the "features/folder/" path
    And a file named "World.feature" with:
    """
      Feature: World consistency
        In order to maintain stable behaviors
        As a features developer
        I want, that "World" flushes between scenarios

        Background:
          Given I have entered 10

        Scenario: Subtracting
          Then I must have 10
          And I subtract the value 6
          Then I must have 4
      """
    And I am in the "../../" path
    When I run "behat --no-ansi -f junit --out test"
    Then it should pass
    And file "test/TEST-World.xml" should exist
    And file "test/TEST-folder-World.xml" should exist
