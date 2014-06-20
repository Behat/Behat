Feature: Pretty Formatter
  In order to debug features
  As a feature writer
  I need to have pretty formatter

  Scenario: Complex
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\CustomSnippetAcceptingContext,
          Behat\Behat\Tester\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

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
          Given I must have 10
          When I add <value>
          Then I must have <result>

          Examples:
            | value | result |
            |  5    | 16     |
            |  10   | 20     |
            |  23   | 32     |
      """
    When I run "behat --no-colors -f pretty"
    Then it should fail with:
      """
      Feature: World consistency
        In order to maintain stable behaviors
        As a features developer
        I want, that "World" flushes between scenarios

        Background:               # features/World.feature:6
          Given I have entered 10 # FeatureContext::iHaveEntered()

        Scenario: Undefined   # features/World.feature:9
          Then I must have 10 # FeatureContext::iMustHave()
          And Something new
          Then I must have 10 # FeatureContext::iMustHave()

        Scenario: Pending            # features/World.feature:14
          Then I must have 10        # FeatureContext::iMustHave()
          And Something not done yet # FeatureContext::somethingNotDoneYet()
            TODO: write pending definition
          Then I must have 10        # FeatureContext::iMustHave()

        Scenario: Failed      # features/World.feature:19
          When I add 4        # FeatureContext::iAdd()
          Then I must have 13 # FeatureContext::iMustHave()
            Failed asserting that 14 matches expected '13'.

        Scenario Outline: Passed & Failed # features/World.feature:23
          Given I must have 10            # FeatureContext::iMustHave()
          When I add <value>              # FeatureContext::iAdd()
          Then I must have <result>       # FeatureContext::iMustHave()

          Examples:
            | value | result |
            | 5     | 16     |
              Failed asserting that 15 matches expected '16'.
            | 10    | 20     |
            | 23    | 32     |
              Failed asserting that 33 matches expected '32'.

      --- Failed scenarios:

          features/World.feature:19
          features/World.feature:30
          features/World.feature:32

      6 scenarios (1 passed, 3 failed, 1 undefined, 1 pending)
      23 steps (16 passed, 3 failed, 1 undefined, 1 pending, 2 skipped)

      --- FeatureContext has missing steps. Define them with these snippets:

          /**
           * @Then /^Something new$/
           */
          public function somethingNew()
          {
              throw new PendingException();
          }
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
          public function iAddOrSubtract($op, $num) {
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
    When I run "behat --no-colors -f pretty"
    Then it should pass with:
      """
      Feature: World consistency
        In order to maintain stable behaviors
        As a features developer
        I want, that "World" flushes between scenarios

        Background:               # features/World.feature:6
          Given I have entered 10 # FeatureContext::iHaveEntered()

        Scenario: Adding some interesting # features/World.feature:9
                  value
          Then I must have 10             # FeatureContext::iMustHave()
          And I add the value 6           # FeatureContext::iAddOrSubtract()
          Then I must have 16             # FeatureContext::iMustHave()

        Scenario: Subtracting        # features/World.feature:15
                  some
                  value
          Then I must have 10        # FeatureContext::iMustHave()
          And I subtract the value 6 # FeatureContext::iAddOrSubtract()
          Then I must have 4         # FeatureContext::iMustHave()

      2 scenarios (2 passed)
      8 steps (8 passed)
      """

    Scenario: Don't print undefined exceptions in outline
      Given a file named "features/bootstrap/FeatureContext.php" with:
        """
        <?php

        use Behat\Behat\Context\Context;
        use Behat\Gherkin\Node\PyStringNode,
            Behat\Gherkin\Node\TableNode;

        class FeatureContext implements Context
        {
            private $value = 10;

            /**
             * @Then /I must have "([^"]+)"/
             */
            public function iMustHave($num) {
                PHPUnit_Framework_Assert::assertEquals(intval(preg_replace('/[^\d]+/', '', $num)), $this->value);
            }

            /**
             * @When /I add "([^"]+)"/
             */
            public function iAdd($num) {
                $this->value += intval(preg_replace('/[^\d]+/', '', $num));
            }
        }
        """
      And a file named "features/ls.feature" with:
        """
        Feature: ls
          In order to see the directory structure
          As a UNIX user
          I need to be able to list the current directory's contents

          Background:
            Given I have a file named "foo"

          Scenario: List 2 files in a directory
            Given I have a file named "bar"
            When I run "ls"
            Then I should see "bar" in output
            And I should see "foo" in output

          Scenario: List 1 file and 1 dir
            Given I have a directory named "dir"
            When I run "ls"
            Then I should see "dir" in output
            And I should see "foo" in output

          Scenario Outline:
            Given I have a <object> named "<name>"
            When I run "ls"
            Then I should see "<name>" in output
            And I should see "foo" in output

            Examples:
              | object    | name |
              | file      | bar  |
              | directory | dir  |
        """
      When I run "behat --no-colors features/ls.feature --no-snippets"
      Then it should pass with:
        """
        Feature: ls
          In order to see the directory structure
          As a UNIX user
          I need to be able to list the current directory's contents

          Background:                       # features/ls.feature:6
            Given I have a file named "foo"

          Scenario: List 2 files in a directory # features/ls.feature:9
            Given I have a file named "bar"
            When I run "ls"
            Then I should see "bar" in output
            And I should see "foo" in output

          Scenario: List 1 file and 1 dir        # features/ls.feature:15
            Given I have a directory named "dir"
            When I run "ls"
            Then I should see "dir" in output
            And I should see "foo" in output

          Scenario Outline:                        # features/ls.feature:21
            Given I have a <object> named "<name>"
            When I run "ls"
            Then I should see "<name>" in output
            And I should see "foo" in output

            Examples:
              | object    | name |
              | file      | bar  |
              | directory | dir  |

        4 scenarios (4 undefined)
        20 steps (20 undefined)
        """

  Scenario: Multiline titles
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context,
          Behat\Behat\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeatureContext implements Context
      {}
      """
    And a file named "features/World.feature" with:
      """
      Feature: World consistency
        In order to maintain stable behaviors
        As a features developer
        I want, that "World" flushes between scenarios

        Background: Some background
          title
            with
        multiple lines

          Given I have entered 10

        Scenario: Undefined
                  scenario or
                  whatever
          Then I must have 10
          And Something new
          Then I must have 10

      Scenario Outline: Passed & Failed
      steps and other interesting stuff
        he-he-he

          Given I must have 10
          When I add <value>
          Then I must have <result>

          Examples:
            | value | result |
            |  5    | 16     |
            |  10   | 20     |
            |  23   | 32     |
      """
    When I run "behat --no-colors -f pretty --no-snippets"
    Then it should pass with:
      """
      Feature: World consistency
        In order to maintain stable behaviors
        As a features developer
        I want, that "World" flushes between scenarios

        Background: Some background # features/World.feature:6
          title
            with
          multiple lines
          Given I have entered 10

        Scenario: Undefined   # features/World.feature:13
                  scenario or
                  whatever
          Then I must have 10
          And Something new
          Then I must have 10

        Scenario Outline: Passed & Failed # features/World.feature:20
          steps and other interesting stuff
          he-he-he
          Given I must have 10
          When I add <value>
          Then I must have <result>

          Examples:
            | value | result |
            | 5     | 16     |
            | 10    | 20     |
            | 23    | 32     |

      4 scenarios (4 undefined)
      16 steps (16 undefined)
      """

  Scenario: Background with failing step and 2 scenarios
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;
      use Behat\Behat\Tester\Exception\PendingException;

      class FeatureContext implements Context
      {
          /**
           * @Given /^.*$/
           */
          public function anything() {
              throw new PendingException();
          }
      }
      """
    And a file named "features/test.feature" with:
      """
      Feature: Customer can see the cost of their purchase in basket
        In order to see the cost of my purchase
        As a customer
        I need to see the totals of my basket

        Background:
          Given there are the following products in the catalog
            | name     | price |
            | trousers | 12    |

        Scenario: £12 delivery £3
          Given I have an empty basket
          When I add the product "trousers" to my basket

        Scenario: £12 delivery £3
          Given I have an empty basket
          When I add the product "trousers" to my basket
      """
    When I run "behat --no-colors -f pretty --no-snippets"
    Then it should pass with:
      """
      Feature: Customer can see the cost of their purchase in basket
        In order to see the cost of my purchase
        As a customer
        I need to see the totals of my basket

        Background:                                             # features/test.feature:6
          Given there are the following products in the catalog # FeatureContext::anything()
            | name     | price |
            | trousers | 12    |
            TODO: write pending definition

        Scenario: £12 delivery £3                        # features/test.feature:11
          Given I have an empty basket                   # FeatureContext::anything()
          When I add the product "trousers" to my basket # FeatureContext::anything()

        Scenario: £12 delivery £3                        # features/test.feature:15
          Given there are the following products in the catalog # FeatureContext::anything()
            | name     | price |
            | trousers | 12    |
            TODO: write pending definition
          Given I have an empty basket                   # FeatureContext::anything()
          When I add the product "trousers" to my basket # FeatureContext::anything()

      2 scenarios (2 pending)
      6 steps (2 pending, 4 skipped)
      """
