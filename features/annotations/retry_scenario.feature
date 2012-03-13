Feature: Multiple tries
  In order to test unstable behavior like mink browser tests
  As a feature developer
  I need to have a --retry-step option

  Scenario: Pass after multiple retry attempts
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\BehatContext;

      require_once 'PHPUnit/Autoload.php';
      require_once 'PHPUnit/Framework/Assert/Functions.php';

      class FeatureContext extends BehatContext
      {

          private static $retryAttempt = 0;

          private $apples = 0;

          /**
           * @Given /^I have (\d+) apples$/
           */
          public function iHaveApples($argument1)
          {
              $this->apples = $argument1;
          }

          /**
           * @When /^I ate (\d+) apple$/
           */
          public function iAteApple($argument1)
          {
              $this->apples = $this->apples - $argument1;
          }

          /**
           * @Then /^I should have (\d+) apples but this will pass after (\d+) retry attempts$/
           */
          public function iShouldHaveApplesButThisWillPassAfterRetryAttempts($apples, $attempt)
          {
              if (self::$retryAttempt < $attempt) {
                  self::$retryAttempt++;
                  assertTrue(false);
              }
              assertEquals($apples, $this->apples);
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

        Scenario: I'm little hungry and need 2 attempts to eat the apple
          When I ate 1 apple
          Then I should have 2 apples but this will pass after 1 retry attempts
      """
    When I run "behat -f pretty --retry-scenario 1"
    Then it should pass with:
      """
      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Background:             # features/apples.feature:6
          Given I have 3 apples # FeatureContext::iHaveApples()

        Scenario: I'm little hungry and need 2 attempts to eat the apple        # features/apples.feature:9
          When I ate 1 apple                                                    # FeatureContext::iAteApple()
          Then I should have 2 apples but this will pass after 1 retry attempts # FeatureContext::iShouldHaveApplesButThisWillPassAfterRetryAttempts()
            Failed asserting that false is true.

        Scenario: I'm little hungry and need 2 attempts to eat the apple        # features/apples.feature:9
          When I ate 1 apple                                                    # FeatureContext::iAteApple()
          Then I should have 2 apples but this will pass after 1 retry attempts # FeatureContext::iShouldHaveApplesButThisWillPassAfterRetryAttempts()

      2 scenarios (1 passed, 1 unstable)
      6 steps (5 passed, 1 unstable)
      """

  Scenario: Fail after multiple retry attempts
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\BehatContext;

      require_once 'PHPUnit/Autoload.php';
      require_once 'PHPUnit/Framework/Assert/Functions.php';

      class FeatureContext extends BehatContext
      {

          private static $retryAttempt = 0;

          private $apples = 0;

          /**
           * @Given /^I have (\d+) apples$/
           */
          public function iHaveApples($argument1)
          {
              $this->apples = $argument1;
          }

          /**
           * @When /^I ate (\d+) apple$/
           */
          public function iAteApple($argument1)
          {
              $this->apples = $this->apples - $argument1;
          }

          /**
           * @Then /^I should have (\d+) apples but this will pass after (\d+) retry attempts$/
           */
          public function iShouldHaveApplesButThisWillPassAfterRetryAttempts($apples, $attempt)
          {
              if (self::$retryAttempt < $attempt) {
                  self::$retryAttempt++;
                  assertTrue(false);
              }
              assertEquals($apples, $this->apples);
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

        Scenario: I'm little hungry and need 2 attempts to eat the apple
          When I ate 1 apple
          Then I should have 2 apples but this will pass after 1 retry attempts
      """
    When I run "behat -f pretty --retry-scenario 0"
    Then it should fail with:
      """
      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Background:             # features/apples.feature:6
          Given I have 3 apples # FeatureContext::iHaveApples()

        Scenario: I'm little hungry and need 2 attempts to eat the apple        # features/apples.feature:9
          When I ate 1 apple                                                    # FeatureContext::iAteApple()
          Then I should have 2 apples but this will pass after 1 retry attempts # FeatureContext::iShouldHaveApplesButThisWillPassAfterRetryAttempts()
            Failed asserting that false is true.

      1 scenario (1 failed)
      3 steps (2 passed, 1 failed)
      """

  Scenario: Pass after multiple background attempts
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\BehatContext;

      require_once 'PHPUnit/Autoload.php';
      require_once 'PHPUnit/Framework/Assert/Functions.php';

      class FeatureContext extends BehatContext
      {
          private static $retryAttempt = 0;
          private $apples = 0;

          /**
           * @Given /^I have (\d+) apples$/
           */
          public function iHaveApples($argument1)
          {
              if (self::$retryAttempt < 1) {
                  self::$retryAttempt++;
                  assertTrue(false);
              }
              $this->apples = $argument1;
          }

          /**
           * @When /^I ate (\d+) apple$/
           */
          public function iAteApple($argument1)
          {
              $this->apples = $this->apples - $argument1;
          }

          /**
           * @Then /^I should have (\d+) apples$/
           */
          public function iShouldHaveApplesButThisWillPassAfterRetryAttempts($apples)
          {
              assertEquals($apples, $this->apples);
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

        Scenario:
          When I ate 1 apple
          Then I should have 2 apples
      """
    When I run "behat -f pretty --retry-scenario 1"
    Then it should pass with:
      """
      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Background:             # features/apples.feature:6
          Given I have 3 apples # FeatureContext::iHaveApples()
            Failed asserting that false is true.

        Scenario:                     # features/apples.feature:9
          When I ate 1 apple          # FeatureContext::iAteApple()
          Then I should have 2 apples # FeatureContext::iShouldHaveApplesButThisWillPassAfterRetryAttempts()

        Scenario:                     # features/apples.feature:9
          When I ate 1 apple          # FeatureContext::iAteApple()
          Then I should have 2 apples # FeatureContext::iShouldHaveApplesButThisWillPassAfterRetryAttempts()

      2 scenarios (1 passed, 1 unstable)
      6 steps (3 passed, 3 unstable)
      """

  Scenario: Fail after multiple background attempts
    Given a file named "features/bootstrap/FeatureContext.php" with:
    """
    <?php

    use Behat\Behat\Context\BehatContext;

    require_once 'PHPUnit/Autoload.php';
    require_once 'PHPUnit/Framework/Assert/Functions.php';

    class FeatureContext extends BehatContext
    {
        private static $retryAttempt = 0;
        private $apples = 0;

        /**
        * @Given /^I have (\d+) apples$/
        */
        public function iHaveApples($argument1)
        {
          if (self::$retryAttempt < 1) {
              self::$retryAttempt++;
              assertTrue(false);
          }
          $this->apples = $argument1;
        }

        /**
        * @When /^I ate (\d+) apple$/
        */
        public function iAteApple($argument1)
        {
          $this->apples = $this->apples - $argument1;
        }

        /**
        * @Then /^I should have (\d+) apples$/
        */
        public function iShouldHaveApplesButThisWillPassAfterRetryAttempts($apples)
        {
          assertEquals($apples, $this->apples);
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

        Scenario:
          When I ate 1 apple
          Then I should have 2 apples
    """
    When I run "behat -f pretty --retry-scenario 0"
    Then it should fail with:
    """
    Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

      Background:             # features/apples.feature:6
        Given I have 3 apples # FeatureContext::iHaveApples()
          Failed asserting that false is true.

      Scenario:                     # features/apples.feature:9
        When I ate 1 apple          # FeatureContext::iAteApple()
        Then I should have 2 apples # FeatureContext::iShouldHaveApplesButThisWillPassAfterRetryAttempts()

    1 scenario (1 failed)
    3 steps (2 skipped, 1 failed)
    """

  Scenario: Pass after multiple outline attempts
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\BehatContext;

      require_once 'PHPUnit/Autoload.php';
      require_once 'PHPUnit/Framework/Assert/Functions.php';

      class FeatureContext extends BehatContext
      {

          private static $retryAttempt = 0;

          private $apples = 0;

          /**
           * @Given /^I have (\d+) apples$/
           */
          public function iHaveApples($argument1)
          {
              $this->apples = $argument1;
          }

          /**
           * @When /^I ate (\d+) apple$/
           */
          public function iAteApple($argument1)
          {
              $this->apples = $this->apples - $argument1;
          }

          /**
           * @Then /^I should have (\d+) apples but this will pass after (\d+) retry attempts$/
           */
          public function iShouldHaveApplesButThisWillPassAfterRetryAttempts($apples, $attempt)
          {
              if (self::$retryAttempt < $attempt) {
                  self::$retryAttempt++;
                  assertTrue(false);
              }
              assertEquals($apples, $this->apples);
          }
      }
      """
    And a file named "features/apples.feature" with:
      """
      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Scenario Outline: I'm little hungry and need 2 attempts to eat the apple
          Given I have 3 apples
          When I ate 1 apple
          Then I should have <apples> apples but this will pass after <attempts> retry attempts
          Examples:
            | apples | attempts |
            | 2      | 1        |
      """
    When I run "behat --retry-scenario 1 -fprogress"
    Then it should pass with:
      """
      ..*...

      2 scenarios (1 passed, 1 unstable)
      6 steps (5 passed, 1 unstable)
      """

  Scenario: Fail after multiple outline attempts
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\BehatContext;

      require_once 'PHPUnit/Autoload.php';
      require_once 'PHPUnit/Framework/Assert/Functions.php';

      class FeatureContext extends BehatContext
      {

          private static $retryAttempt = 0;

          private $apples = 0;

          /**
           * @Given /^I have (\d+) apples$/
           */
          public function iHaveApples($argument1)
          {
              $this->apples = $argument1;
          }

          /**
           * @When /^I ate (\d+) apple$/
           */
          public function iAteApple($argument1)
          {
              $this->apples = $this->apples - $argument1;
          }

          /**
           * @Then /^I should have (\d+) apples but this will pass after (\d+) retry attempts$/
           */
          public function iShouldHaveApplesButThisWillPassAfterRetryAttempts($apples, $attempt)
          {
              if (self::$retryAttempt < $attempt) {
                  self::$retryAttempt++;
                  assertTrue(false);
              }
              assertEquals($apples, $this->apples);
          }
      }
      """
    And a file named "features/apples.feature" with:
      """
      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Scenario Outline: I'm little hungry and need 2 attempts to eat the apple
          Given I have 3 apples
          When I ate 1 apple
          Then I should have <apples> apples but this will pass after <attempts> retry attempts
          Examples:
            | apples | attempts |
            | 2      | 1        |
      """
    When I run "behat --retry-scenario 0 -fprogress"
    Then it should fail with:
      """
      ..F

      (::) failed steps (::)

      01. Failed asserting that false is true.
          In step `Then I should have 2 apples but this will pass after 1 retry attempts'. # FeatureContext::iShouldHaveApplesButThisWillPassAfterRetryAttempts()
          From scenario `I'm little hungry and need 2 attempts to eat the apple'.          # features/apples.feature:6

      1 scenario (1 failed)
      3 steps (2 passed, 1 failed)
      """
