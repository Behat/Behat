  Feature: JUnit Formatter
  In order integrate with other development tools
  As a developer
  I need to be able to generate a JUnit-compatible report

  Scenario: Normal Scenario's
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\CustomSnippetAcceptingContext,
          Behat\Behat\Tester\Exception\PendingException;

      class FeatureContext implements CustomSnippetAcceptingContext
      {
          private $value;

          public static function getAcceptedSnippetType() { return 'regex'; }

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
          When I add <value>
          Then I must have <result>

          Examples:
            | value | result |
            |  5    | 16     |
            |  10   | 20     |
            |  23   | 32     |

      Scenario Outline: Another Outline
        When I add <value>
        Then I must have <result>

        Examples:
          | value | result |
          | 5     | 15     |
          | 10    | 20     |
      """
    When I run "behat --no-colors -f junit -o junit"
    Then it should fail with:
      """
      --- FeatureContext has missing steps. Define them with these snippets:

          /**
           * @Then /^Something new$/
           */
          public function somethingNew()
          {
              throw new PendingException();
          }
      """
    And "junit/default.xml" file xml should be like:
      """
      <?xml version="1.0" encoding="UTF-8"?>
      <testsuites name="default">
        <testsuite name="World consistency" tests="8" skipped="0" failures="3" errors="2">
          <testcase name="Undefined" status="undefined">
            <error message="And Something new" type="undefined"/>
          </testcase>
          <testcase name="Pending" status="pending">
            <error message="And Something not done yet: TODO: write pending definition" type="pending"/>
          </testcase>
          <testcase name="Failed" status="failed">
            <failure message="Then I must have 13: Failed asserting that 14 matches expected '13'."/>
          </testcase>
          <testcase name="Passed &amp; Failed #1" status="failed">
            <failure message="Then I must have 16: Failed asserting that 15 matches expected '16'."/>
          </testcase>
          <testcase name="Passed &amp; Failed #2" status="passed"/>
          <testcase name="Passed &amp; Failed #3" status="failed">
            <failure message="Then I must have 32: Failed asserting that 33 matches expected '32'."/>
          </testcase>
          <testcase name="Another Outline #1" status="passed"/>
          <testcase name="Another Outline #2" status="passed"/>
        </testsuite>
      </testsuites>
      """
    And the file "junit/default.xml" should be a valid document according to "junit.xsd"

  Scenario: Multiple Features
    Given a file named "features/bootstrap/FeatureContext.php" with:
    """
      <?php

      use Behat\Behat\Context\CustomSnippetAcceptingContext,
          Behat\Behat\Tester\Exception\PendingException;

      class FeatureContext implements CustomSnippetAcceptingContext
      {
          private $value;

          public static function getAcceptedSnippetType() { return 'regex'; }

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
    And a file named "features/adding_feature_1.feature" with:
      """
      Feature: Adding Feature 1
        In order to add number together
        As a mathematician
        I want, something that acts like a calculator

        Scenario: Adding 4 to 10
          Given I have entered 10
          When I add 4
          Then I must have 14
      """
    And a file named "features/adding_feature_2.feature" with:
      """
      Feature: Adding Feature 2
        In order to add number together
        As a mathematician
        I want, something that acts like a calculator

        Scenario: Adding 8 to 10
          Given I have entered 10
          When I add 8
          Then I must have 18
      """
    When I run "behat --no-colors -f junit -o junit"
    And "junit/default.xml" file xml should be like:
      """
      <?xml version="1.0" encoding="UTF-8"?>
      <testsuites name="default">
        <testsuite name="Adding Feature 1" tests="1" skipped="0" failures="0" errors="0">
          <testcase name="Adding 4 to 10" status="passed"></testcase>
        </testsuite>
        <testsuite name="Adding Feature 2" tests="1" skipped="0" failures="0" errors="0">
          <testcase name="Adding 8 to 10" status="passed"></testcase>
        </testsuite>
      </testsuites>
      """
    And the file "junit/default.xml" should be a valid document according to "junit.xsd"

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
    And "junit/default.xml" file xml should be like:
      """
      <?xml version="1.0" encoding="UTF-8"?>
      <testsuites name="default">
        <testsuite name="World consistency" tests="2" skipped="0" failures="0" errors="0">
          <testcase name="Adding some interesting value" status="passed"/>
          <testcase name="Subtracting some value" status="passed"/>
        </testsuite>
      </testsuites>
      """
    And the file "junit/default.xml" should be a valid document according to "junit.xsd"

  Scenario: Multiple suites
    Given a file named "features/bootstrap/SmallKidContext.php" with:
      """
      <?php

      use Behat\Behat\Context\CustomSnippetAcceptingContext;

      class SmallKidContext implements CustomSnippetAcceptingContext
      {
          protected $strongLevel;

          public static function getAcceptedSnippetType() { return 'regex'; }

          /**
           * @Given I am not strong
           */
          public function iAmNotStrong() {
              $this->strongLevel = 0;
          }

          /**
           * @When /I eat an apple/
           */
          public function iEatAnApple() {
              $this->strongLevel += 2;
          }

          /**
           * @Then /I will be stronger/
           */
          public function iWillBeStronger() {
              PHPUnit_Framework_Assert::assertNotEquals(0, $this->strongLevel);
          }
      }
      """
    And a file named "features/bootstrap/OldManContext.php" with:
    """
      <?php

      use Behat\Behat\Context\CustomSnippetAcceptingContext;

      class OldManContext implements CustomSnippetAcceptingContext
      {
          protected $strongLevel;

          public static function getAcceptedSnippetType() { return 'regex'; }

          /**
           * @Given I am not strong
           */
          public function iAmNotStrong() {
              $this->strongLevel = 0;
          }

          /**
           * @When /I eat an apple/
           */
          public function iEatAnApple() { }

          /**
           * @Then /I will be stronger/
           */
          public function iWillBeStronger() {
              PHPUnit_Framework_Assert::assertNotEquals(0, $this->strongLevel);
          }
      }
      """
    And a file named "features/apple_eating_smallkid.feature" with:
      """
      Feature: Apple Eating
        In order to be stronger
        As a small kid
        I want to get stronger from eating apples

        Background:
          Given I am not strong

        Scenario: Eating one apple
          When I eat an apple
          Then I will be stronger
      """
    And a file named "features/apple_eating_oldmen.feature" with:
    """
      Feature: Apple Eating
        In order to be stronger
        As an old man
        I want to get stronger from eating apples

        Background:
          Given I am not strong

        Scenario: Eating one apple
          When I eat an apple
          Then I will be stronger
      """
    And a file named "behat.yml" with:
      """
      default:
          suites:
              small_kid:
                  contexts: [SmallKidContext]
                  filters:
                    role: small kid
                  path: %paths.base%/features
              old_man:
                  contexts: [OldManContext]
                  path: %paths.base%/features
                  filters:
                    role: old man
      """
    When I run "behat --no-colors -f junit -o junit"
    Then it should fail with no output
    And "junit/small_kid.xml" file xml should be like:
      """
      <?xml version="1.0" encoding="UTF-8"?>
      <testsuites name="small_kid">
        <testsuite name="Apple Eating" tests="1" skipped="0" failures="0" errors="0">
          <testcase name="Eating one apple" status="passed"/>
        </testsuite>
      </testsuites>
      """
    And the file "junit/small_kid.xml" should be a valid document according to "junit.xsd"
    And "junit/old_man.xml" file xml should be like:
      """
      <?xml version="1.0" encoding="UTF-8"?>
      <testsuites name="old_man">
        <testsuite name="Apple Eating" tests="1" skipped="0" failures="1" errors="0">
          <testcase name="Eating one apple" status="failed">
            <failure message="Then I will be stronger: Failed asserting that 0 is not equal to 0."/>
          </testcase>
        </testsuite>
      </testsuites>
      """
    And the file "junit/old_man.xml" should be a valid document according to "junit.xsd"

  Scenario: Report skipped testcases
    Given a file named "features/bootstrap/FeatureContext.php" with:
    """
      <?php

      use Behat\Behat\Context\CustomSnippetAcceptingContext,
          Behat\Behat\Tester\Exception\PendingException;

      class FeatureContext implements CustomSnippetAcceptingContext
      {
          private $value;

          public static function getAcceptedSnippetType() { return 'regex'; }

          /**
           * @BeforeScenario
           */
          public function setup() {
            throw new \Exception();
          }

          /**
           * @Given /I have entered (\d+)/
           * @Then /^I must have (\d+)$/
           */
          public function action($num)
          {
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

        Scenario: Skipped
          Then I must have 10

        Scenario: Another skipped
          Then I must have 10

      """
    When I run "behat --no-colors -f junit -o junit"
    And "junit/default.xml" file xml should be like:
      """
      <?xml version="1.0" encoding="UTF-8"?>
      <testsuites name="default">
        <testsuite name="World consistency" tests="2" skipped="2" failures="0" errors="0">
          <testcase name="Skipped" status="skipped" />
          <testcase name="Another skipped" status="skipped" />
        </testsuite>
      </testsuites>
      """
    And the file "junit/default.xml" should be a valid document according to "junit.xsd"

  Scenario: Stop on Failure
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\CustomSnippetAcceptingContext,
          Behat\Behat\Tester\Exception\PendingException;

      class FeatureContext implements CustomSnippetAcceptingContext
      {
          private $value;

          public static function getAcceptedSnippetType() { return 'regex'; }

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

        Scenario: Failed
          When I add 4
          Then I must have 13
      """
    When I run "behat --no-colors -f junit -o junit"
    Then it should fail with no output
    And "junit/default.xml" file xml should be like:
      """
      <?xml version="1.0" encoding="UTF-8"?>
      <testsuites name="default">
        <testsuite name="World consistency" tests="1" skipped="0" failures="1" errors="0">
          <testcase name="Failed" status="failed">
            <failure message="Then I must have 13: Failed asserting that 14 matches expected '13'."/>
          </testcase>
        </testsuite>
      </testsuites>
      """
    And the file "junit/default.xml" should be a valid document according to "junit.xsd"

  @php-version @php5.3 @php5.4
  Scenario: Aborting due to PHP error
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\CustomSnippetAcceptingContext,
          Behat\Behat\Tester\Exception\PendingException;

      class FeatureContext implements CustomSnippetAcceptingContext
      {
          private $value;

          public static function getAcceptedSnippetType() { return 'regex'; }

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
              PHPUnit_Framework_Assert::assertEqual($num, $this->value);
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

        Scenario: Failed
          When I add 4
          Then I must have 14
      """
    When I run "behat --no-colors -f junit -o junit"
    Then it should fail with:
      """
      Call to undefined method PHPUnit_Framework_Assert::assertEqual
      """
    And "junit/default.xml" file xml should be like:
      """
      <?xml version="1.0" encoding="UTF-8"?>
      <testsuites name="default"/>
      """

  Scenario: Aborting due invalid output path
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\CustomSnippetAcceptingContext,
          Behat\Behat\Tester\Exception\PendingException;

      class FeatureContext implements CustomSnippetAcceptingContext
      {
        public static function getAcceptedSnippetType() { return 'regex'; }
      }
      """
    And a file named "junit.txt" with:
      """
      """
    When I run "behat --no-colors -f junit -o junit.txt"
    Then it should fail with:
      """
      [Behat\Testwork\Output\Exception\BadOutputPathException]
        Directory expected for the `output_path` option, but a filename was given.
      """
