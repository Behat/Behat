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
    When I run "behat -f pretty"
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
            Failed asserting that <integer:14> is equal to <string:13>.

        Scenario Outline: Passed & Failed # features/World.feature:23
          Given I must have 10            # FeatureContext::iMustHave()
          When I add <value>              # FeatureContext::iAdd()
          Then I must have <result>       # FeatureContext::iMustHave()

          Examples:
            | value | result |
            | 5     | 16     |
              Failed asserting that <integer:15> is equal to <string:16>.
            | 10    | 20     |
            | 23    | 32     |
              Failed asserting that <integer:33> is equal to <string:32>.

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

        Scenario: Subtracting
          Then I must have 10
          And I subtract the value 6
          Then I must have 4
      """
    When I run "behat -f pretty --colors"
    Then it should pass with:
      """
      Feature: World consistency
        In order to maintain stable behaviors
        As a features developer
        I want, that "World" flushes between scenarios

        Background:               [30m# features/World.feature:6[0m
          [32mGiven I have entered [0m[32;1m10[0m[32m[0m [30m# FeatureContext::iHaveEntered()[0m

        Scenario: Adding          [30m# features/World.feature:9[0m
          [32mThen I must have [0m[32;1m10[0m[32m[0m     [30m# FeatureContext::iMustHave()[0m
          [32mAnd I [0m[32;1madd[0m[32m the value [0m[32;1m6[0m[32m[0m   [30m# FeatureContext::iAddOrSubstact()[0m
          [32mThen I must have [0m[32;1m16[0m[32m[0m     [30m# FeatureContext::iMustHave()[0m

        Scenario: Subtracting        [30m# features/World.feature:14[0m
          [32mThen I must have [0m[32;1m10[0m[32m[0m        [30m# FeatureContext::iMustHave()[0m
          [32mAnd I [0m[32;1msubtract[0m[32m the value [0m[32;1m6[0m[32m[0m [30m# FeatureContext::iAddOrSubstact()[0m
          [32mThen I must have [0m[32;1m4[0m[32m[0m         [30m# FeatureContext::iMustHave()[0m

      2 scenarios ([32m2 passed[0m)
      8 steps ([32m8 passed[0m)
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
    When I run "behat -f pretty"
    Then it should pass with:
      """
      Feature: World consistency
        In order to maintain stable behaviors
        As a features developer
        I want, that "World" flushes between scenarios

        Background:               # features/World.feature:6
          Given I have entered 10 # FeatureContext::iHaveEntered()

        Scenario: Adding some interesting
                  value           # features/World.feature:9
          Then I must have 10     # FeatureContext::iMustHave()
          And I add the value 6   # FeatureContext::iAddOrSubstact()
          Then I must have 16     # FeatureContext::iMustHave()

        Scenario: Subtracting
                  some
                  value              # features/World.feature:15
          Then I must have 10        # FeatureContext::iMustHave()
          And I subtract the value 6 # FeatureContext::iAddOrSubstact()
          Then I must have 4         # FeatureContext::iMustHave()

      2 scenarios (2 passed)
      8 steps (8 passed)
      """
