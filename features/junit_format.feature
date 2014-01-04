Feature: JUnit Formatter
  In order to debug features
  As a feature writer
  I need to have JUnit formatter

  Scenario: Complex
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\RegexAcceptingContext,
          Behat\Behat\Tester\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeatureContext implements RegexAcceptingContext
      {
          private $value;

          /**
           * @Given /I have entered (\d+)/
           */
          public function iHaveEntered($num) {
              $this->value = $num;
          }

          /**
           * @Then /I must have (\d+)/
           */
          public function iMustHave($num) {
              PHPUnit_Framework_Assert::assertEquals($num, $this->value);
          }

          /**
           * @When /I add (\d+)/
           */
          public function iAdd($num) {
              $this->value += $num;
          }

          /**
           * @When /^Something not done yet$/
           */
          public function somethingNotDoneYet() {
              throw new PendingException();
          }
      }
      """
    And a file named "features/World.feature" with:
      """
      Feature: World consistency
        In order to maintain stable behaviors
        As a features developer
        I want, that "World" flushes between scenarios

        Background:
          Given I have entered 10

        Scenario: Undefined
          Then I must have 10
          And Something new
          Then I must have 10

        Scenario: Pending
          Then I must have 10
          And Something not done yet
          Then I must have 10

        Scenario: Failed
          When I add 4
          Then I must have 13

        Scenario Outline: Passed & Failed
          Given I must have 10
          When I add <value>
          Then I must have <result>

          Examples:
            | value | result |
            |  5    | 16     |
            |  10   | 20     |
            |  23   | 32     |
      """
    When I run "behat --no-colors -f junit -o junit"
    Then it should fail with:
      """
      --- FeatureContext has missing steps. Define them with these snippets:

          /**
           * @Given /^Something new$/
           */
          public function somethingNew()
          {
              throw new PendingException();
          }
      """
    And "junit/default.xml" file should contain:
      """
      <?xml version="1.0"?>
      <testsuites name="default">
        <testsuite name="World consistency" file="features/World.feature" tests="6" failures="3" errors="2">
          <testcase name="Undefined" assertions="4" status="UNDEFINED">
            <error type="undefined" message="And Something new"/>
            <skipped>Then I must have 10</skipped>
          </testcase>
          <testcase name="Pending" assertions="4" status="PENDING">
            <skipped>And Something not done yet: TODO: write pending definition</skipped>
            <skipped>Then I must have 10</skipped>
          </testcase>
          <testcase name="Failed" assertions="3" status="FAILED">
            <failure message="Then I must have 13: Failed asserting that 14 matches expected '13'."/>
          </testcase>
          <testcase name="Passed &amp; Failed #1" assertions="4" status="FAILED">
            <failure message="Then I must have 16: Failed asserting that 15 matches expected '16'."/>
          </testcase>
          <testcase name="Passed &amp; Failed #2" assertions="4" status="PASSED"/>
          <testcase name="Passed &amp; Failed #3" assertions="4" status="FAILED">
            <failure message="Then I must have 32: Failed asserting that 33 matches expected '32'."/>
          </testcase>
        </testsuite>
      </testsuites>
      """

  Scenario: Multiline titles
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          private $value;

          /**
           * @Given /I have entered (\d+)/
           */
          public function iHaveEntered($num) {
              $this->value = $num;
          }

          /**
           * @Then /I must have (\d+)/
           */
          public function iMustHave($num) {
              PHPUnit_Framework_Assert::assertEquals($num, $this->value);
          }

          /**
           * @When /I (add|subtract) the value (\d+)/
           */
          public function iAddOrSubstact($op, $num) {
              if ($op == 'add')
                $this->value += $num;
              elseif ($op == 'subtract')
                $this->value -= $num;
          }
      }
      """
    And a file named "features/World.feature" with:
      """
      Feature: World consistency
        In order to maintain stable behaviors
        As a features developer
        I want, that "World" flushes between scenarios

        Background:
          Given I have entered 10

        Scenario: Adding some interesting
                  value
          Then I must have 10
          And I add the value 6
          Then I must have 16

        Scenario: Subtracting
                  some
                  value
          Then I must have 10
          And I subtract the value 6
          Then I must have 4
      """
    When I run "behat --no-colors -f junit -o junit"
    Then it should pass with no output
    And "junit/default.xml" file should contain:
      """
      <?xml version="1.0"?>
      <testsuites name="default">
        <testsuite name="World consistency" file="features/World.feature" tests="2" failures="0" errors="0">
          <testcase name="Adding some interesting value" assertions="4" status="PASSED"/>
          <testcase name="Subtracting some value" assertions="4" status="PASSED"/>
        </testsuite>
      </testsuites>
      """

  Scenario: Multiple suites
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\RegexAcceptingContext,
          Behat\Behat\Tester\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeatureContext implements RegexAcceptingContext
      {
          private $value;

          /**
           * @Given /I have entered (\d+)/
           */
          public function iHaveEntered($num) {
              $this->value = $num;
          }

          /**
           * @Then /I must have (\d+)/
           */
          public function iMustHave($num) {
              PHPUnit_Framework_Assert::assertEquals($num, $this->value);
          }

          /**
           * @When /I add (\d+)/
           */
          public function iAdd($num) {
              $this->value += $num;
          }
      }
      """
    And a file named "features/World.feature" with:
      """
      Feature: World consistency
        In order to maintain stable behaviors
        As a features developer
        I want, that "World" flushes between scenarios

        Background:
          Given I have entered 10

        Scenario: Passing
          When I add 10
          Then I must have 20
      """
    And a file named "features/User.feature" with:
      """
      Feature: User consistency
        In order to maintain stable behaviors
        As a features developer
        I want, that "World" flushes between scenarios

        Background:
          Given I have entered 10

        Scenario: Passing
          When I add 20
          Then I must have 30
      """
    And a file named "behat.yml" with:
      """
      default:
          suites:
              world:
                  context: FeatureContext
                  path: %paths.base%/features/World.feature
              user:
                  context: FeatureContext
                  path: %paths.base%/features/User.feature
      """
    When I run "behat --no-colors -f junit -o junit"
    Then it should pass with no output
    And "junit/world.xml" file should contain:
      """
      <?xml version="1.0"?>
      <testsuites name="world">
        <testsuite name="World consistency" file="features/World.feature" tests="1" failures="0" errors="0">
          <testcase name="Passing" assertions="3" status="PASSED"/>
        </testsuite>
      </testsuites>
      """
    And "junit/user.xml" file should contain:
      """
      <?xml version="1.0"?>
      <testsuites name="user">
        <testsuite name="User consistency" file="features/User.feature" tests="1" failures="0" errors="0">
          <testcase name="Passing" assertions="3" status="PASSED"/>
        </testsuite>
      </testsuites>
      """

  Scenario: Output in hooks and steps
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          private $value;

          /**
           * @Given /I have entered (\d+)/
           */
          public function iHaveEntered($num) {
              $this->value = $num;
          }

          /**
           * @Then /I must have (\d+)/
           */
          public function iMustHave($num) {
              PHPUnit_Framework_Assert::assertEquals($num, $this->value);
          }

          /**
           * @When /I (add|subtract) the value (\d+)/
           */
          public function iAddOrSubstact($op, $num) {
              if ($op == 'add')
                $this->value += $num;
              elseif ($op == 'subtract')
                $this->value -= $num;
          }

          /**
           * @When /I produce some output/
           */
          public function iProduceOutput() {
              echo 'output from step!';
              throw new Exception('Failed');
          }
      }
      """
    And a file named "features/World.feature" with:
      """
      Feature: World consistency
        In order to maintain stable behaviors
        As a features developer
        I want, that "World" flushes between scenarios

        Background:
          Given I have entered 10

        Scenario: Adding some interesting value
          Then I must have 10
          And I add the value 6
          Then I must have 16

        Scenario: Subtracting some value
          Then I must have 10
          And I subtract the value 6
          And I produce some output
          Then I must have 4
      """
    When I run "behat --no-colors -f junit -o junit"
    Then it should fail with no output
    And "junit/default.xml" file should contain:
      """
      <?xml version="1.0"?>
      <testsuites name="default">
        <testsuite name="World consistency" file="features/World.feature" tests="2" failures="1" errors="0">
          <testcase name="Adding some interesting value" assertions="4" status="PASSED"/>
          <testcase name="Subtracting some value" assertions="5" status="FAILED">
            <failure message="And I produce some output: Failed">output from step!</failure>
            <skipped>Then I must have 4</skipped>
          </testcase>
        </testsuite>
      </testsuites>
      """
