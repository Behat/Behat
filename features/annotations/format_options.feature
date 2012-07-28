Feature: Format options
  In order to optimize behat output
  As a tester
  I need to be able to set options on behat runner

  Background:
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      require_once 'PHPUnit/Autoload.php';
      require_once 'PHPUnit/Framework/Assert/Functions.php';

      use Behat\Behat\Context\BehatContext,
          Behat\Behat\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeatureContext extends BehatContext
      {
          private $apples = 0;
          private $parameters;

          public function __construct(array $parameters) {
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
              assertEquals(intval($count), $this->apples);
          }

          /**
           * @Then /^context parameter "([^"]*)" should be equal to "([^"]*)"$/
           */
          public function contextParameterShouldBeEqualTo($key, $val) {
            assertEquals($val, $this->parameters[$key]);
          }

          /**
           * @Given /^context parameter "([^"]*)" should be array with (\d+) elements$/
           */
          public function contextParameterShouldBeArrayWithElements($key, $count) {
              assertInternalType('array', $this->parameters[$key]);
              assertEquals(2, count($this->parameters[$key]));
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

  Scenario: --ansi option
    When I run "behat --ansi"
    Then it should fail with:
      """
      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Background:             [30m# features/apples.feature:6[0m
          [32mGiven I have [0m[32;1m3[0m[32m apples[0m [30m# FeatureContext::iHaveApples()[0m

        Scenario: I'm little hungry   [30m# features/apples.feature:9[0m
          [32mWhen I ate [0m[32;1m1[0m[32m apple[0m          [30m# FeatureContext::iAteApples()[0m
          [31mThen I should have [0m[31;1m3[0m[31m apples[0m [30m# FeatureContext::iShouldHaveApples()[0m
            [31mFailed asserting that 2 matches expected 3.[0m

        Scenario: Found more apples   [30m# features/apples.feature:13[0m
          [32mWhen I found [0m[32;1m5[0m[32m apples[0m       [30m# FeatureContext::iFoundApples()[0m
          [32mThen I should have [0m[32;1m8[0m[32m apples[0m [30m# FeatureContext::iShouldHaveApples()[0m

        Scenario: Found more apples   [30m# features/apples.feature:17[0m
          [32mWhen I found [0m[32;1m2[0m[32m apples[0m       [30m# FeatureContext::iFoundApples()[0m
          [32mThen I should have [0m[32;1m5[0m[32m apples[0m [30m# FeatureContext::iShouldHaveApples()[0m
          [33mAnd do something undefined[0m

        Scenario Outline: Other situations   [30m# features/apples.feature:22[0m
          [36mWhen I ate [0m[36;1m<ate>[0m[36m apples[0m            [30m# FeatureContext::iAteApples()[0m
          [36mAnd I found [0m[36;1m<found>[0m[36m apples[0m         [30m# FeatureContext::iFoundApples()[0m
          [36mThen I should have [0m[36;1m<result>[0m[36m apples[0m [30m# FeatureContext::iShouldHaveApples()[0m

          Examples:
      [36m[0m[36m      [0m[36m[0m|[36m[0m[36m ate [0m[36m[0m|[36m[0m[36m found [0m[36m[0m|[36m[0m[36m result [0m[36m[0m|[36m[0m
      [32m[0m[32m      [0m[32m[0m|[32m[0m[32m 3   [0m[32m[0m|[32m[0m[32m 1     [0m[32m[0m|[32m[0m[32m 1      [0m[32m[0m|[32m[0m
      [31m[0m[31m      [0m[31m[0m|[31m[0m[31m 0   [0m[31m[0m|[31m[0m[31m 4     [0m[31m[0m|[31m[0m[31m 8      [0m[31m[0m|[31m[0m
              [31mFailed asserting that 7 matches expected 8.[0m
      [32m[0m[32m      [0m[32m[0m|[32m[0m[32m 2   [0m[32m[0m|[32m[0m[32m 2     [0m[32m[0m|[32m[0m[32m 3      [0m[32m[0m|[32m[0m

        Scenario: Multilines                 [30m# features/apples.feature:33[0m
          [33mGiven pystring:[0m
      [33m      '''
            some pystring
            '''[0m
          [33mAnd table:[0m
      [33m      | col1 | col2 |
            | val1 | val2 |[0m

      7 scenarios ([32m3 passed[0m, [33m2 undefined[0m, [31m2 failed[0m)
      25 steps ([32m20 passed[0m, [33m3 undefined[0m, [31m2 failed[0m)

      [33mYou can implement step definitions for undefined steps with these snippets:[0m

      [33m    /**
           * @Given /^do something undefined$/
           */
          public function doSomethingUndefined()
          {
              throw new PendingException();
          }[0m

      [33m    /**
           * @Given /^pystring:$/
           */
          public function pystring(PyStringNode $string)
          {
              throw new PendingException();
          }[0m

      [33m    /**
           * @Given /^table:$/
           */
          public function table(TableNode $table)
          {
              throw new PendingException();
          }[0m
      """

  Scenario: --no-ansi option
    When I run "behat --no-ansi"
    Then it should fail with:
      """
      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Background:             # features/apples.feature:6
          Given I have 3 apples # FeatureContext::iHaveApples()

        Scenario: I'm little hungry   # features/apples.feature:9
          When I ate 1 apple          # FeatureContext::iAteApples()
          Then I should have 3 apples # FeatureContext::iShouldHaveApples()
            Failed asserting that 2 matches expected 3.

        Scenario: Found more apples   # features/apples.feature:13
          When I found 5 apples       # FeatureContext::iFoundApples()
          Then I should have 8 apples # FeatureContext::iShouldHaveApples()

        Scenario: Found more apples   # features/apples.feature:17
          When I found 2 apples       # FeatureContext::iFoundApples()
          Then I should have 5 apples # FeatureContext::iShouldHaveApples()
          And do something undefined

        Scenario Outline: Other situations   # features/apples.feature:22
          When I ate <ate> apples            # FeatureContext::iAteApples()
          And I found <found> apples         # FeatureContext::iFoundApples()
          Then I should have <result> apples # FeatureContext::iShouldHaveApples()

          Examples:
            | ate | found | result |
            | 3   | 1     | 1      |
            | 0   | 4     | 8      |
              Failed asserting that 7 matches expected 8.
            | 2   | 2     | 3      |

        Scenario: Multilines                 # features/apples.feature:33
          Given pystring:
            '''
            some pystring
            '''
          And table:
            | col1 | col2 |
            | val1 | val2 |

      7 scenarios (3 passed, 2 undefined, 2 failed)
      25 steps (20 passed, 3 undefined, 2 failed)

      You can implement step definitions for undefined steps with these snippets:

          /**
           * @Given /^do something undefined$/
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

  Scenario: --no-paths option
    When I run "behat --no-ansi --no-paths"
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
            '''
            some pystring
            '''
          And table:
            | col1 | col2 |
            | val1 | val2 |

      7 scenarios (3 passed, 2 undefined, 2 failed)
      25 steps (20 passed, 3 undefined, 2 failed)

      You can implement step definitions for undefined steps with these snippets:

          /**
           * @Given /^do something undefined$/
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

  Scenario: --no-snippets option
    When I run "behat --no-ansi --no-snippets"
    Then it should fail with:
      """
      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Background:             # features/apples.feature:6
          Given I have 3 apples # FeatureContext::iHaveApples()

        Scenario: I'm little hungry   # features/apples.feature:9
          When I ate 1 apple          # FeatureContext::iAteApples()
          Then I should have 3 apples # FeatureContext::iShouldHaveApples()
            Failed asserting that 2 matches expected 3.

        Scenario: Found more apples   # features/apples.feature:13
          When I found 5 apples       # FeatureContext::iFoundApples()
          Then I should have 8 apples # FeatureContext::iShouldHaveApples()

        Scenario: Found more apples   # features/apples.feature:17
          When I found 2 apples       # FeatureContext::iFoundApples()
          Then I should have 5 apples # FeatureContext::iShouldHaveApples()
          And do something undefined

        Scenario Outline: Other situations   # features/apples.feature:22
          When I ate <ate> apples            # FeatureContext::iAteApples()
          And I found <found> apples         # FeatureContext::iFoundApples()
          Then I should have <result> apples # FeatureContext::iShouldHaveApples()

          Examples:
            | ate | found | result |
            | 3   | 1     | 1      |
            | 0   | 4     | 8      |
              Failed asserting that 7 matches expected 8.
            | 2   | 2     | 3      |

        Scenario: Multilines                 # features/apples.feature:33
          Given pystring:
            '''
            some pystring
            '''
          And table:
            | col1 | col2 |
            | val1 | val2 |

      7 scenarios (3 passed, 2 undefined, 2 failed)
      25 steps (20 passed, 3 undefined, 2 failed)
      """

  Scenario: --snippets-paths option
    When I run "behat --no-ansi --snippets-paths"
    Then it should fail with:
      """
      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Background:             # features/apples.feature:6
          Given I have 3 apples # FeatureContext::iHaveApples()

        Scenario: I'm little hungry   # features/apples.feature:9
          When I ate 1 apple          # FeatureContext::iAteApples()
          Then I should have 3 apples # FeatureContext::iShouldHaveApples()
            Failed asserting that 2 matches expected 3.

        Scenario: Found more apples   # features/apples.feature:13
          When I found 5 apples       # FeatureContext::iFoundApples()
          Then I should have 8 apples # FeatureContext::iShouldHaveApples()

        Scenario: Found more apples   # features/apples.feature:17
          When I found 2 apples       # FeatureContext::iFoundApples()
          Then I should have 5 apples # FeatureContext::iShouldHaveApples()
          And do something undefined

        Scenario Outline: Other situations   # features/apples.feature:22
          When I ate <ate> apples            # FeatureContext::iAteApples()
          And I found <found> apples         # FeatureContext::iFoundApples()
          Then I should have <result> apples # FeatureContext::iShouldHaveApples()

          Examples:
            | ate | found | result |
            | 3   | 1     | 1      |
            | 0   | 4     | 8      |
              Failed asserting that 7 matches expected 8.
            | 2   | 2     | 3      |

        Scenario: Multilines                 # features/apples.feature:33
          Given pystring:
            '''
            some pystring
            '''
          And table:
            | col1 | col2 |
            | val1 | val2 |

      7 scenarios (3 passed, 2 undefined, 2 failed)
      25 steps (20 passed, 3 undefined, 2 failed)

      You can implement step definitions for undefined steps with these snippets:

          /**
           * And do something undefined # features/apples.feature:20
           *
           * @Given /^do something undefined$/
           */
          public function doSomethingUndefined()
          {
              throw new PendingException();
          }

          /**
           * Given pystring: # features/apples.feature:34
           *
           * @Given /^pystring:$/
           */
          public function pystring(PyStringNode $string)
          {
              throw new PendingException();
          }

          /**
           * And table: # features/apples.feature:38
           *
           * @Given /^table:$/
           */
          public function table(TableNode $table)
          {
              throw new PendingException();
          }
      """

  Scenario: --expand option
    When I run "behat --no-ansi --expand"
    Then it should fail with:
      """
      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Background:             # features/apples.feature:6
          Given I have 3 apples # FeatureContext::iHaveApples()

        Scenario: I'm little hungry   # features/apples.feature:9
          When I ate 1 apple          # FeatureContext::iAteApples()
          Then I should have 3 apples # FeatureContext::iShouldHaveApples()
            Failed asserting that 2 matches expected 3.

        Scenario: Found more apples   # features/apples.feature:13
          When I found 5 apples       # FeatureContext::iFoundApples()
          Then I should have 8 apples # FeatureContext::iShouldHaveApples()

        Scenario: Found more apples   # features/apples.feature:17
          When I found 2 apples       # FeatureContext::iFoundApples()
          Then I should have 5 apples # FeatureContext::iShouldHaveApples()
          And do something undefined

        Scenario Outline: Other situations   # features/apples.feature:22
          When I ate <ate> apples            # FeatureContext::iAteApples()
          And I found <found> apples         # FeatureContext::iFoundApples()
          Then I should have <result> apples # FeatureContext::iShouldHaveApples()

            Examples: | 3 | 1 | 1 |
              When I ate 3 apples            # FeatureContext::iAteApples()
              And I found 1 apples           # FeatureContext::iFoundApples()
              Then I should have 1 apples    # FeatureContext::iShouldHaveApples()

            Examples: | 0 | 4 | 8 |
              When I ate 0 apples            # FeatureContext::iAteApples()
              And I found 4 apples           # FeatureContext::iFoundApples()
              Then I should have 8 apples    # FeatureContext::iShouldHaveApples()
                Failed asserting that 7 matches expected 8.

            Examples: | 2 | 2 | 3 |
              When I ate 2 apples            # FeatureContext::iAteApples()
              And I found 2 apples           # FeatureContext::iFoundApples()
              Then I should have 3 apples    # FeatureContext::iShouldHaveApples()

        Scenario: Multilines                 # features/apples.feature:33
          Given pystring:
            '''
            some pystring
            '''
          And table:
            | col1 | col2 |
            | val1 | val2 |

      7 scenarios (3 passed, 2 undefined, 2 failed)
      25 steps (20 passed, 3 undefined, 2 failed)

      You can implement step definitions for undefined steps with these snippets:

          /**
           * @Given /^do something undefined$/
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

  Scenario: --no-multiline option
    When I run "behat --no-ansi --no-multiline"
    Then it should fail with:
      """
      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Background:             # features/apples.feature:6
          Given I have 3 apples # FeatureContext::iHaveApples()

        Scenario: I'm little hungry   # features/apples.feature:9
          When I ate 1 apple          # FeatureContext::iAteApples()
          Then I should have 3 apples # FeatureContext::iShouldHaveApples()
            Failed asserting that 2 matches expected 3.

        Scenario: Found more apples   # features/apples.feature:13
          When I found 5 apples       # FeatureContext::iFoundApples()
          Then I should have 8 apples # FeatureContext::iShouldHaveApples()

        Scenario: Found more apples   # features/apples.feature:17
          When I found 2 apples       # FeatureContext::iFoundApples()
          Then I should have 5 apples # FeatureContext::iShouldHaveApples()
          And do something undefined

        Scenario Outline: Other situations   # features/apples.feature:22
          When I ate <ate> apples            # FeatureContext::iAteApples()
          And I found <found> apples         # FeatureContext::iFoundApples()
          Then I should have <result> apples # FeatureContext::iShouldHaveApples()

          Examples:
            | ate | found | result |
            | 3   | 1     | 1      |
            | 0   | 4     | 8      |
              Failed asserting that 7 matches expected 8.
            | 2   | 2     | 3      |

        Scenario: Multilines                 # features/apples.feature:33
          Given pystring:
          And table:

      7 scenarios (3 passed, 2 undefined, 2 failed)
      25 steps (20 passed, 3 undefined, 2 failed)

      You can implement step definitions for undefined steps with these snippets:

          /**
           * @Given /^do something undefined$/
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
