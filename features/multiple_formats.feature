Feature: Multiple formats
  In order to use multiple formats
  As a tester
  I need to be able to specify multiple output formats to behat

  Background:
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\CustomSnippetAcceptingContext,
          Behat\Behat\Tester\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeatureContext implements CustomSnippetAcceptingContext
      {
          private $apples = 0;
          private $parameters;

          public static function getAcceptedSnippetType() { return 'regex'; }

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
          And do something undefined

        Scenario Outline: Other situations
          When I ate <ate> apples
          And I found <found> apples
          Then I should have <result> apples

          Examples:
            | ate | found | result |
            | 3   | 1     | 1      |
            | 0   | 4     | 8      |
            | 2   | 2     | 3      |

        Scenario: Multilines
          Given pystring:
            '''
            some pystring
            '''
          And table:
            | col1 | col2 |
            | val1 | val2 |
      """

  Scenario: 2 formats, default output
    When I run "behat --no-colors -f pretty -f progress --format-settings='{\"multiline\": false}'"
    Then it should fail with:
      """
      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Background:             # features/apples.feature:6
          Given I have 3 apples # FeatureContext::iHaveApples()
      .
        Scenario: I'm little hungry   # features/apples.feature:9
          When I ate 1 apple          # FeatureContext::iAteApples()
      .    Then I should have 3 apples # FeatureContext::iShouldHaveApples()
            Failed asserting that 2 matches expected 3.
      F
        Scenario: Found more apples   # features/apples.feature:13
      .    When I found 5 apples       # FeatureContext::iFoundApples()
      .    Then I should have 8 apples # FeatureContext::iShouldHaveApples()
      .
        Scenario: Found more apples   # features/apples.feature:17
      .    When I found 2 apples       # FeatureContext::iFoundApples()
      .    Then I should have 5 apples # FeatureContext::iShouldHaveApples()
      .    And do something undefined
      U
      ....  Scenario Outline: Other situations   # features/apples.feature:22
          When I ate <ate> apples            # FeatureContext::iAteApples()
          And I found <found> apples         # FeatureContext::iFoundApples()
          Then I should have <result> apples # FeatureContext::iShouldHaveApples()

          Examples:
            | ate | found | result |
            | 3   | 1     | 1      |
      ...F      | 0   | 4     | 8      |
              Failed asserting that 7 matches expected 8.
      ....      | 2   | 2     | 3      |

        Scenario: Multilines # features/apples.feature:33
      .    Given pystring:
            ...
      U    And table:
            ...
      U
      --- Failed scenarios:

          features/apples.feature:9
          features/apples.feature:30

      7 scenarios (3 passed, 2 failed, 2 undefined)
      25 steps (20 passed, 2 failed, 3 undefined)


      --- Failed steps:

          Then I should have 3 apples # features/apples.feature:11
            Failed asserting that 2 matches expected 3.

          Then I should have 8 apples # features/apples.feature:25
            Failed asserting that 7 matches expected 8.

      7 scenarios (3 passed, 2 failed, 2 undefined)
      25 steps (20 passed, 2 failed, 3 undefined)

      --- FeatureContext has missing steps. Define them with these snippets:

          /**
           * @Then /^do something undefined$/
           */
          public function doSomethingUndefined()
          {
              throw new PendingException();
          }

          /**
           * @Given /^pystring:$/
           */
          public function pystring(PyStringNode $string)
          {
              throw new PendingException();
          }

          /**
           * @Given /^table:$/
           */
          public function table(TableNode $table)
          {
              throw new PendingException();
          }
      """

  Scenario: 2 formats, same output
    When I run "behat --no-colors -f pretty -f progress --out=std --format-settings='{\"multiline\": false}'"
    Then it should fail with:
      """
      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Background:             # features/apples.feature:6
          Given I have 3 apples # FeatureContext::iHaveApples()
      .
        Scenario: I'm little hungry   # features/apples.feature:9
          When I ate 1 apple          # FeatureContext::iAteApples()
      .    Then I should have 3 apples # FeatureContext::iShouldHaveApples()
            Failed asserting that 2 matches expected 3.
      F
        Scenario: Found more apples   # features/apples.feature:13
      .    When I found 5 apples       # FeatureContext::iFoundApples()
      .    Then I should have 8 apples # FeatureContext::iShouldHaveApples()
      .
        Scenario: Found more apples   # features/apples.feature:17
      .    When I found 2 apples       # FeatureContext::iFoundApples()
      .    Then I should have 5 apples # FeatureContext::iShouldHaveApples()
      .    And do something undefined
      U
      ....  Scenario Outline: Other situations   # features/apples.feature:22
          When I ate <ate> apples            # FeatureContext::iAteApples()
          And I found <found> apples         # FeatureContext::iFoundApples()
          Then I should have <result> apples # FeatureContext::iShouldHaveApples()

          Examples:
            | ate | found | result |
            | 3   | 1     | 1      |
      ...F      | 0   | 4     | 8      |
              Failed asserting that 7 matches expected 8.
      ....      | 2   | 2     | 3      |

        Scenario: Multilines # features/apples.feature:33
      .    Given pystring:
            ...
      U    And table:
            ...
      U
      --- Failed scenarios:

          features/apples.feature:9
          features/apples.feature:30

      7 scenarios (3 passed, 2 failed, 2 undefined)
      25 steps (20 passed, 2 failed, 3 undefined)


      --- Failed steps:

          Then I should have 3 apples # features/apples.feature:11
            Failed asserting that 2 matches expected 3.

          Then I should have 8 apples # features/apples.feature:25
            Failed asserting that 7 matches expected 8.

      7 scenarios (3 passed, 2 failed, 2 undefined)
      25 steps (20 passed, 2 failed, 3 undefined)

      --- FeatureContext has missing steps. Define them with these snippets:

          /**
           * @Then /^do something undefined$/
           */
          public function doSomethingUndefined()
          {
              throw new PendingException();
          }

          /**
           * @Given /^pystring:$/
           */
          public function pystring(PyStringNode $string)
          {
              throw new PendingException();
          }

          /**
           * @Given /^table:$/
           */
          public function table(TableNode $table)
          {
              throw new PendingException();
          }
      """

  Scenario: 2 formats, write first to file
    When I run "behat --no-colors -f pretty -o apples.pretty -f progress -o std --format-settings='{\"multiline\": false, \"paths\": false}'"
    Then it should fail with:
      """
      ..F......U.......F.....UU

      --- Failed steps:

          Then I should have 3 apples # features/apples.feature:11
            Failed asserting that 2 matches expected 3.

          Then I should have 8 apples # features/apples.feature:25
            Failed asserting that 7 matches expected 8.

      7 scenarios (3 passed, 2 failed, 2 undefined)
      25 steps (20 passed, 2 failed, 3 undefined)

      --- FeatureContext has missing steps. Define them with these snippets:

          /**
           * @Then /^do something undefined$/
           */
          public function doSomethingUndefined()
          {
              throw new PendingException();
          }

          /**
           * @Given /^pystring:$/
           */
          public function pystring(PyStringNode $string)
          {
              throw new PendingException();
          }

          /**
           * @Given /^table:$/
           */
          public function table(TableNode $table)
          {
              throw new PendingException();
          }
      """
    And "apples.pretty" file should contain:
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
            Failed asserting that 2 matches expected 3.

        Scenario: Found more apples
          When I found 5 apples
          Then I should have 8 apples

        Scenario: Found more apples
          When I found 2 apples
          Then I should have 5 apples
          And do something undefined

        Scenario Outline: Other situations
          When I ate <ate> apples
          And I found <found> apples
          Then I should have <result> apples

          Examples:
            | ate | found | result |
            | 3   | 1     | 1      |
            | 0   | 4     | 8      |
              Failed asserting that 7 matches expected 8.
            | 2   | 2     | 3      |

        Scenario: Multilines
          Given pystring:
            ...
          And table:
            ...

      --- Failed scenarios:

          features/apples.feature:9
          features/apples.feature:30

      7 scenarios (3 passed, 2 failed, 2 undefined)
      25 steps (20 passed, 2 failed, 3 undefined)
      """

  Scenario: 2 formats, write second to file
    When I run "behat --no-colors -f pretty -o std --format=progress --out=apples.progress --format-settings='{\"multiline\": false, \"paths\": false}'"
    Then it should fail with:
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
            Failed asserting that 2 matches expected 3.

        Scenario: Found more apples
          When I found 5 apples
          Then I should have 8 apples

        Scenario: Found more apples
          When I found 2 apples
          Then I should have 5 apples
          And do something undefined

        Scenario Outline: Other situations
          When I ate <ate> apples
          And I found <found> apples
          Then I should have <result> apples

          Examples:
            | ate | found | result |
            | 3   | 1     | 1      |
            | 0   | 4     | 8      |
              Failed asserting that 7 matches expected 8.
            | 2   | 2     | 3      |

        Scenario: Multilines
          Given pystring:
            ...
          And table:
            ...

      --- Failed scenarios:

          features/apples.feature:9
          features/apples.feature:30

      7 scenarios (3 passed, 2 failed, 2 undefined)
      25 steps (20 passed, 2 failed, 3 undefined)

      --- FeatureContext has missing steps. Define them with these snippets:

          /**
           * @Then /^do something undefined$/
           */
          public function doSomethingUndefined()
          {
              throw new PendingException();
          }

          /**
           * @Given /^pystring:$/
           */
          public function pystring(PyStringNode $string)
          {
              throw new PendingException();
          }

          /**
           * @Given /^table:$/
           */
          public function table(TableNode $table)
          {
              throw new PendingException();
          }
      """
    And "apples.progress" file should contain:
      """
      ..F......U.......F.....UU

      --- Failed steps:

          Then I should have 3 apples # features/apples.feature:11
            Failed asserting that 2 matches expected 3.

          Then I should have 8 apples # features/apples.feature:25
            Failed asserting that 7 matches expected 8.

      7 scenarios (3 passed, 2 failed, 2 undefined)
      25 steps (20 passed, 2 failed, 3 undefined)
      """

  Scenario: 2 formats, write both to files
    When I run "behat --no-colors -f pretty -o app.pretty -f progress -o app.progress --format-settings='{\"multiline\": false, \"paths\": false}'"
    Then it should fail with:
      """
      --- FeatureContext has missing steps. Define them with these snippets:

          /**
           * @Then /^do something undefined$/
           */
          public function doSomethingUndefined()
          {
              throw new PendingException();
          }

          /**
           * @Given /^pystring:$/
           */
          public function pystring(PyStringNode $string)
          {
              throw new PendingException();
          }

          /**
           * @Given /^table:$/
           */
          public function table(TableNode $table)
          {
              throw new PendingException();
          }
      """
    And "app.pretty" file should contain:
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
            Failed asserting that 2 matches expected 3.

        Scenario: Found more apples
          When I found 5 apples
          Then I should have 8 apples

        Scenario: Found more apples
          When I found 2 apples
          Then I should have 5 apples
          And do something undefined

        Scenario Outline: Other situations
          When I ate <ate> apples
          And I found <found> apples
          Then I should have <result> apples

          Examples:
            | ate | found | result |
            | 3   | 1     | 1      |
            | 0   | 4     | 8      |
              Failed asserting that 7 matches expected 8.
            | 2   | 2     | 3      |

        Scenario: Multilines
          Given pystring:
            ...
          And table:
            ...

      --- Failed scenarios:

          features/apples.feature:9
          features/apples.feature:30

      7 scenarios (3 passed, 2 failed, 2 undefined)
      25 steps (20 passed, 2 failed, 3 undefined)
      """
    And "app.progress" file should contain:
      """
      ..F......U.......F.....UU

      --- Failed steps:

          Then I should have 3 apples # features/apples.feature:11
            Failed asserting that 2 matches expected 3.

          Then I should have 8 apples # features/apples.feature:25
            Failed asserting that 7 matches expected 8.

      7 scenarios (3 passed, 2 failed, 2 undefined)
      25 steps (20 passed, 2 failed, 3 undefined)
      """
