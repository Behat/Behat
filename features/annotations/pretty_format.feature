Feature: Pretty Formatter
  In order to debug features
  As a feature writer
  I need to have pretty formatter

  Background:
    Given a file named "features/bootstrap/bootstrap.php" with:
      """
      <?php
      require_once 'PHPUnit/Autoload.php';
      require_once 'PHPUnit/Framework/Assert/Functions.php';
      """

  Scenario: Complex
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\BehatContext,
          Behat\Behat\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeatureContext extends BehatContext
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
              assertEquals($num, $this->value);
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
    When I run "behat --no-ansi -f pretty"
    Then it should fail with:
      """
      Feature: World consistency
        In order to maintain stable behaviors
        As a features developer
        I want, that "World" flushes between scenarios

        Background:               # features/World.feature:6
          Given I have entered 10 # FeatureContext::iHaveEntered()

        Scenario: Undefined       # features/World.feature:9
          Then I must have 10     # FeatureContext::iMustHave()
          And Something new
          Then I must have 10     # FeatureContext::iMustHave()

        Scenario: Pending            # features/World.feature:14
          Then I must have 10        # FeatureContext::iMustHave()
          And Something not done yet # FeatureContext::somethingNotDoneYet()
            TODO: write pending definition
          Then I must have 10        # FeatureContext::iMustHave()

        Scenario: Failed             # features/World.feature:19
          When I add 4               # FeatureContext::iAdd()
          Then I must have 13        # FeatureContext::iMustHave()
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

      6 scenarios (1 passed, 1 pending, 1 undefined, 3 failed)
      23 steps (16 passed, 2 skipped, 1 pending, 1 undefined, 3 failed)

      You can implement step definitions for undefined steps with these snippets:

          /**
           * @Given /^Something new$/
           */
          public function somethingNew()
          {
              throw new PendingException();
          }
      """

  Scenario: Multiple parameters
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\BehatContext, Behat\Behat\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,  Behat\Gherkin\Node\TableNode;

      class FeatureContext extends BehatContext
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
              assertEquals($num, $this->value);
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
           * @When /^regex (?P<pattern>\/([^\/]|\\\/)*\/)$/
           */
          public function somethingNotDoneYet($pattern) {}
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

        Scenario: Adding
          Then I must have 10
          And I add the value 6
          Then I must have 16
          And regex /some\/regex_ha/

        Scenario: Subtracting
          Then I must have 10
          And I subtract the value 6
          Then I must have 4
      """
    When I run "behat -f pretty --ansi"
    Then it should pass with:
      """
      Feature: World consistency
        In order to maintain stable behaviors
        As a features developer
        I want, that "World" flushes between scenarios

        Background:               [30m# features/World.feature:6[0m
          [32mGiven I have entered [0m[32;1m10[0m[32m[0m [30m# FeatureContext::iHaveEntered()[0m

        Scenario: Adding             [30m# features/World.feature:9[0m
          [32mThen I must have [0m[32;1m10[0m[32m[0m        [30m# FeatureContext::iMustHave()[0m
          [32mAnd I [0m[32;1madd[0m[32m the value [0m[32;1m6[0m[32m[0m      [30m# FeatureContext::iAddOrSubstact()[0m
          [32mThen I must have [0m[32;1m16[0m[32m[0m        [30m# FeatureContext::iMustHave()[0m
          [32mAnd regex [0m[32;1m/some\/regex_ha/[0m[32m[0m [30m# FeatureContext::somethingNotDoneYet()[0m

        Scenario: Subtracting        [30m# features/World.feature:15[0m
          [32mThen I must have [0m[32;1m10[0m[32m[0m        [30m# FeatureContext::iMustHave()[0m
          [32mAnd I [0m[32;1msubtract[0m[32m the value [0m[32;1m6[0m[32m[0m [30m# FeatureContext::iAddOrSubstact()[0m
          [32mThen I must have [0m[32;1m4[0m[32m[0m         [30m# FeatureContext::iMustHave()[0m

      2 scenarios ([32m2 passed[0m)
      9 steps ([32m9 passed[0m)
      """

  Scenario: Multiline titles
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\BehatContext, Behat\Behat\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,  Behat\Gherkin\Node\TableNode;

      class FeatureContext extends BehatContext
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
              assertEquals($num, $this->value);
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
    When I run "behat --no-ansi -f pretty"
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
          And I add the value 6           # FeatureContext::iAddOrSubstact()
          Then I must have 16             # FeatureContext::iMustHave()

        Scenario: Subtracting             # features/World.feature:15
                  some
                  value
          Then I must have 10             # FeatureContext::iMustHave()
          And I subtract the value 6      # FeatureContext::iAddOrSubstact()
          Then I must have 4              # FeatureContext::iMustHave()

      2 scenarios (2 passed)
      8 steps (8 passed)
      """

  Scenario: Outline parameter inside step argument
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\BehatContext,
          Behat\Behat\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeatureContext extends BehatContext
      {
          private $value = 10;

          /**
           * @Then /I must have "([^"]+)"/
           */
          public function iMustHave($num) {
              assertEquals(intval(preg_replace('/[^\d]+/', '', $num)), $this->value);
          }

          /**
           * @When /I add "([^"]+)"/
           */
          public function iAdd($num) {
              $this->value += intval(preg_replace('/[^\d]+/', '', $num));
          }
      }
      """
    And a file named "features/World.feature" with:
      """
      Feature: World consistency
        In order to maintain stable behaviors
        As a features developer
        I want, that "World" flushes between scenarios

        Scenario Outline: Passed & Failed
          When I add "amount of <value> something"
          Then I must have "amount of <result> something"

          Examples:
            | value | result |
            |  5    | 15     |
            |  10   | 20     |
            |  23   | 33     |
      """
    When I run "behat -f pretty --ansi"
    Then it should pass with:
      """
      Feature: World consistency
        In order to maintain stable behaviors
        As a features developer
        I want, that "World" flushes between scenarios

        Scenario Outline: Passed & Failed                 [30m# features/World.feature:6[0m
          [36mWhen I add "amount of [0m[36;1m<value>[0m[36m something"[0m        [30m# FeatureContext::iAdd()[0m
          [36mThen I must have "amount of [0m[36;1m<result>[0m[36m something"[0m [30m# FeatureContext::iMustHave()[0m

          Examples:
      [36m[0m[36m      [0m[36m[0m|[36m[0m[36m value [0m[36m[0m|[36m[0m[36m result [0m[36m[0m|[36m[0m
      [32m[0m[32m      [0m[32m[0m|[32m[0m[32m 5     [0m[32m[0m|[32m[0m[32m 15     [0m[32m[0m|[32m[0m
      [32m[0m[32m      [0m[32m[0m|[32m[0m[32m 10    [0m[32m[0m|[32m[0m[32m 20     [0m[32m[0m|[32m[0m
      [32m[0m[32m      [0m[32m[0m|[32m[0m[32m 23    [0m[32m[0m|[32m[0m[32m 33     [0m[32m[0m|[32m[0m

      3 scenarios ([32m3 passed[0m)
      6 steps ([32m6 passed[0m)
      """

    Scenario: Don't print undefined exceptions in outline
      Given a file named "features/bootstrap/FeatureContext.php" with:
        """
        <?php

        use Behat\Behat\Context\BehatContext,
            Behat\Behat\Exception\PendingException;
        use Behat\Gherkin\Node\PyStringNode,
            Behat\Gherkin\Node\TableNode;

        class FeatureContext extends BehatContext
        {
            private $value = 10;

            /**
             * @Then /I must have "([^"]+)"/
             */
            public function iMustHave($num) {
                assertEquals(intval(preg_replace('/[^\d]+/', '', $num)), $this->value);
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
      When I run "behat --no-ansi features/ls.feature --no-snippets"
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

      use Behat\Behat\Context\BehatContext,
          Behat\Behat\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeatureContext extends BehatContext
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
    When I run "behat --no-ansi -f pretty --no-snippets"
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

        Scenario: Undefined         # features/World.feature:13
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
