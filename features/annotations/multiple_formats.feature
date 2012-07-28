Feature: Multiple formats
  In order to use multiple formats
  As a tester
  I need to be able to specify multiple output formats to behat

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

  Scenario: 2 formats, default output
    When I run "behat --no-ansi -f pretty,progress --no-multiline"
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
        Scenario Outline: Other situations   # features/apples.feature:22
      ....    When I ate <ate> apples            # FeatureContext::iAteApples()
          And I found <found> apples         # FeatureContext::iFoundApples()
          Then I should have <result> apples # FeatureContext::iShouldHaveApples()

          Examples:
            | ate | found | result |
            | 3   | 1     | 1      |
      ...F      | 0   | 4     | 8      |
              Failed asserting that 7 matches expected 8.
      ....      | 2   | 2     | 3      |

        Scenario: Multilines                 # features/apples.feature:33
      .    Given pystring:
      U    And table:
      U
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



      (::) failed steps (::)

      01. Failed asserting that 2 matches expected 3.
          In step `Then I should have 3 apples'. # FeatureContext::iShouldHaveApples()
          From scenario `I'm little hungry'.     # features/apples.feature:9
          Of feature `Apples story'.             # features/apples.feature

      02. Failed asserting that 7 matches expected 8.
          In step `Then I should have 8 apples'. # FeatureContext::iShouldHaveApples()
          From scenario `Other situations'.      # features/apples.feature:22
          Of feature `Apples story'.             # features/apples.feature

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

  Scenario: 2 formats, same output
    When I run "behat --no-ansi -f pretty,progress --out=, --no-multiline"
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
        Scenario Outline: Other situations   # features/apples.feature:22
      ....    When I ate <ate> apples            # FeatureContext::iAteApples()
          And I found <found> apples         # FeatureContext::iFoundApples()
          Then I should have <result> apples # FeatureContext::iShouldHaveApples()

          Examples:
            | ate | found | result |
            | 3   | 1     | 1      |
      ...F      | 0   | 4     | 8      |
              Failed asserting that 7 matches expected 8.
      ....      | 2   | 2     | 3      |

        Scenario: Multilines                 # features/apples.feature:33
      .    Given pystring:
      U    And table:
      U
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



      (::) failed steps (::)

      01. Failed asserting that 2 matches expected 3.
          In step `Then I should have 3 apples'. # FeatureContext::iShouldHaveApples()
          From scenario `I'm little hungry'.     # features/apples.feature:9
          Of feature `Apples story'.             # features/apples.feature

      02. Failed asserting that 7 matches expected 8.
          In step `Then I should have 8 apples'. # FeatureContext::iShouldHaveApples()
          From scenario `Other situations'.      # features/apples.feature:22
          Of feature `Apples story'.             # features/apples.feature

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

  Scenario: 2 formats, write first to file
    When I run "behat --no-ansi -f pretty,progress --out=apples.pretty, --no-multiline --no-paths"
    Then it should fail with:
      """
      ..F......U.......F.....UU

      (::) failed steps (::)

      01. Failed asserting that 2 matches expected 3.
          In step `Then I should have 3 apples'. # FeatureContext::iShouldHaveApples()
          From scenario `I'm little hungry'.     # features/apples.feature:9
          Of feature `Apples story'.             # features/apples.feature

      02. Failed asserting that 7 matches expected 8.
          In step `Then I should have 8 apples'. # FeatureContext::iShouldHaveApples()
          From scenario `Other situations'.      # features/apples.feature:22
          Of feature `Apples story'.             # features/apples.feature

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

  Scenario: 2 formats, write second to file
    When I run "behat --no-ansi -f pretty,progress --out=,apples.progress --no-multiline --no-paths"
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
    And "apples.progress" file should contain:
      """
      ..F......U.......F.....UU

      (::) failed steps (::)

      01. Failed asserting that 2 matches expected 3.
          In step `Then I should have 3 apples'. # FeatureContext::iShouldHaveApples()
          From scenario `I'm little hungry'.     # features/apples.feature:9
          Of feature `Apples story'.             # features/apples.feature

      02. Failed asserting that 7 matches expected 8.
          In step `Then I should have 8 apples'. # FeatureContext::iShouldHaveApples()
          From scenario `Other situations'.      # features/apples.feature:22
          Of feature `Apples story'.             # features/apples.feature

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

  Scenario: 2 formats, write both to files
    When I run "behat --no-ansi -f pretty,progress --out=app.pretty,app.progress --no-multiline --no-paths"
    Then it should fail with:
      """
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
    And "app.progress" file should contain:
      """
      ..F......U.......F.....UU

      (::) failed steps (::)

      01. Failed asserting that 2 matches expected 3.
          In step `Then I should have 3 apples'. # FeatureContext::iShouldHaveApples()
          From scenario `I'm little hungry'.     # features/apples.feature:9
          Of feature `Apples story'.             # features/apples.feature

      02. Failed asserting that 7 matches expected 8.
          In step `Then I should have 8 apples'. # FeatureContext::iShouldHaveApples()
          From scenario `Other situations'.      # features/apples.feature:22
          Of feature `Apples story'.             # features/apples.feature

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
