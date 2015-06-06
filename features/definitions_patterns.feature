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

  Scenario: Definition parameter with default null value
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          /**
           * @Given I don't provide parameter
           * @Given I can provide parameter :param
           */
          public function parameterCouldBeNull($param = null) {
            PHPUnit_Framework_Assert::assertNull($param);
          }
      }
      """
    And a file named "features/step_patterns.feature" with:
      """
      Feature: Step Pattern
        Scenario:
          Given I don't provide parameter
      """
    When I run "behat -f progress --no-colors"
    Then it should pass with:
      """
      .

      1 scenario (1 passed)
      1 step (1 passed)
      """

  Scenario: Definition parameter with ordered values
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          /**
           * @Given I can provide parameters :someParam and :someParam2
           */
          public function multipleWrongNamedParameters($param1, $param2) {
            PHPUnit_Framework_Assert::assertEquals('one', $param1);
            PHPUnit_Framework_Assert::assertEquals('two', $param2);
          }
      }
      """
    And a file named "features/step_patterns.feature" with:
    """
      Feature: Step Pattern
        Scenario:
          Given I can provide parameters "one" and two
      """
    When I run "behat -f progress --no-colors"
    Then it should pass with:
      """
      .

      1 scenario (1 passed)
      1 step (1 passed)
      """

  Scenario: Definition parameter with both ordered and named values
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          /**
           * @Given I can provide parameters :someParam and :someParam2
           */
          public function multipleWrongNamedParameters($param1, $someParam) {
            PHPUnit_Framework_Assert::assertEquals('two', $param1);
            PHPUnit_Framework_Assert::assertEquals('one', $someParam);
          }
      }
      """
    And a file named "features/step_patterns.feature" with:
      """
      Feature: Step Pattern
        Scenario:
          Given I can provide parameters "one" and two
      """
    When I run "behat -f progress --no-colors"
    Then it should pass with:
      """
      .

      1 scenario (1 passed)
      1 step (1 passed)
      """

  Scenario: Definition parameter with hard mixture of ordered and named values
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          /**
           * @Given I can provide :count parameters :firstParam and :otherParam
           */
          public function multipleWrongNamedParameters($param1, $firstParam, $count) {
            PHPUnit_Framework_Assert::assertEquals('two', $param1);
            PHPUnit_Framework_Assert::assertEquals('one', $firstParam);
            PHPUnit_Framework_Assert::assertEquals(2, $count);
          }
      }
      """
    And a file named "features/step_patterns.feature" with:
      """
      Feature: Step Pattern
        Scenario:
          Given I can provide 2 parameters "one" and two
      """
    When I run "behat -f progress --no-colors"
    Then it should pass with:
      """
      .

      1 scenario (1 passed)
      1 step (1 passed)
      """

  Scenario: Definition parameter with hard mixture of ordered, named values and multiline argument
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          /**
           * @Given I can provide :count parameters :firstParam and :otherParam with:
           */
          public function multipleWrongNamedParameters($param1, $firstParam, $count, $string) {
            PHPUnit_Framework_Assert::assertEquals('two', $param1);
            PHPUnit_Framework_Assert::assertEquals('one', $firstParam);
            PHPUnit_Framework_Assert::assertEquals(2, $count);
            PHPUnit_Framework_Assert::assertEquals("Test", (string) $string);
          }
      }
      """
    And a file named "features/step_patterns.feature" with:
      """
      Feature: Step Pattern
        Scenario:
          Given I can provide 2 parameters "one" and two with:
            '''
            Test
            '''
      """
    When I run "behat -f progress --no-colors"
    Then it should pass with:
      """
      .

      1 scenario (1 passed)
      1 step (1 passed)
      """

  Scenario: Definition parameter followed by colon
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          /**
           * @Given I can provide :count parameters for :name:
           */
          public function multipleWrongNamedParameters($count, $name, $string) {
          PHPUnit_Framework_Assert::assertEquals('2', $count);
            PHPUnit_Framework_Assert::assertEquals('thing', $name);
            PHPUnit_Framework_Assert::assertEquals("Test", (string) $string);
          }
      }
      """
    And a file named "features/step_patterns.feature" with:
      """
      Feature: Step Pattern
        Scenario:
          Given I can provide 2 parameters for "thing":
            '''
            Test
            '''
      """
    When I run "behat -f progress --no-colors"
    Then it should pass with:
      """
      .

      1 scenario (1 passed)
      1 step (1 passed)
      """

  Scenario: Definition parameter with optional parameters
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;
      use Behat\Gherkin\Node\PyStringNode;

      class FeatureContext implements Context
      {
          /**
           * @Then /^the (?:JSON|json)(?: response)?(?: at "(?<path>.*)")? should(?<isNegative> not)? be:$/
           */
          public function checkEquality($path = null, $isNegative = null, PyStringNode $json = null)
          {
              PHPUnit_Framework_Assert::assertNull($path);
              PHPUnit_Framework_Assert::assertNull($isNegative);
              PHPUnit_Framework_Assert::assertNotNull($json);
          }

          /**
           * @Then /^the other (?:JSON|json)(?: response)?(?: at "(?<path>.*)")? should(?<isNegative> not)? be:$/
           */
          public function checkEquality2($json = null, $path = null, $isNegative = null)
          {
              PHPUnit_Framework_Assert::assertNull($path);
              PHPUnit_Framework_Assert::assertNull($isNegative);
              PHPUnit_Framework_Assert::assertNotNull($json);
          }
      }
      """
    And a file named "features/step_patterns.feature" with:
      """
      Feature: Step Pattern
        Scenario:
          Then the JSON should be:
            '''
            Test
            '''
          And the other JSON should be:
            '''
            Test
            '''
      """
    When I run "behat -f progress --no-colors"
    Then it should pass with:
      """
      ..

      1 scenario (1 passed)
      2 steps (2 passed)
      """

  Scenario: Definition parameter with decimal number following string
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          /**
           * @Given I have a package v:version
           */
          public function multipleWrongNamedParameters($version) {
          PHPUnit_Framework_Assert::assertEquals('2.5', $version);
          }
      }
      """
    And a file named "features/step_patterns.feature" with:
      """
      Feature: Step Pattern
        Scenario:
          Given I have a package v2.5
      """
    When I run "behat -f progress --no-colors"
    Then it should pass with:
      """
      .

      1 scenario (1 passed)
      1 step (1 passed)
      """

  Scenario: Empty parameter value
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          /**
           * @When I enter the string :input
           */
          public function multipleWrongNamedParameters($input) {
          PHPUnit_Framework_Assert::assertEquals('', $input);
          }
      }
      """
    And a file named "features/step_patterns.feature" with:
      """
      Feature: Step Pattern
        Scenario:
          When I enter the string ""
      """
    When I run "behat -f progress --no-colors"
    Then it should pass with:
      """
      .

      1 scenario (1 passed)
      1 step (1 passed)
      """

  Scenario: UNIX path as parameter
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          /**
           * @Then images should be uploaded to web\/uploads\/media\/default\/:arg1\/:arg2\/
           */
          public function multipleWrongNamedParameters($arg1, $arg2) {
          PHPUnit_Framework_Assert::assertEquals('0001', $arg1);
          PHPUnit_Framework_Assert::assertEquals('01', $arg2);
          }
      }
      """
    And a file named "features/step_patterns.feature" with:
      """
      Feature: Step Pattern
        Scenario:
          Then images should be uploaded to web/uploads/media/default/0001/01/
      """
    When I run "behat -f progress --no-colors"
    Then it should pass with:
      """
      .

      1 scenario (1 passed)
      1 step (1 passed)
      """

  Scenario: Negative number parameters without quotes
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class FeatureContext implements Context
      {
          /**
           * @Given I have a negative number :num
           */
          public function multipleWrongNamedParameters($num) {
          PHPUnit_Framework_Assert::assertEquals('-3', $num);
          }
      }
      """
    And a file named "features/step_patterns.feature" with:
      """
      Feature: Step Pattern
        Scenario:
          Given I have a negative number -3
      """
    When I run "behat -f progress --no-colors"
    Then it should pass with:
      """
      .

      1 scenario (1 passed)
      1 step (1 passed)
      """
