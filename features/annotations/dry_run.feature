Feature: Dry run
  In order to print formatters output without executing steps
  As a feature developer
  I need to have a --dry-run option

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
          /**
           * @BeforeSuite
           */
          public static function beforeSuite() {
              echo "HOOK: before suite";
          }

          /**
           * @BeforeScenario
           */
          public static function beforeScenario() {
              echo "HOOK: before scenario";
          }

          /**
           * @Given /^I have (\d+) apples?$/
           */
          public function iHaveApples($count) {
              echo "STEP: I have $count apples";
          }

          /**
           * @When /^I ate (\d+) apples?$/
           */
          public function iAteApples($count) {
              echo "STEP: I ate $count apples";
          }

          /**
           * @When /^I found (\d+) apples?$/
           */
          public function iFoundApples($count) {
              echo "STEP: I found $count apples";
          }

          /**
           * @Then /^I should have (\d+) apples$/
           */
          public function iShouldHaveApples($count) {
              echo "STEP: I should have $count apples";
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

        Scenario Outline: Other situations
          When I ate <ate> apples
          And I found <found> apples
          Then I should have <result> apples

          Examples:
            | ate | found | result |
            | 3   | 1     | 1      |
            | 0   | 4     | 8      |
            | 2   | 2     | 3      |
      """

  Scenario: Just run feature
    When I run "behat --no-ansi --no-paths features/apples.feature"
    Then it should pass with:
      """
      HOOK: before suiteFeature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

      HOOK: before scenario  Background:
      STEP: I have 3 apples    Given I have 3 apples

        Scenario: I'm little hungry
      STEP: I ate 1 apples    When I ate 1 apple
      STEP: I should have 3 apples    Then I should have 3 apples

      HOOK: before scenario  Scenario: Found more apples
      STEP: I have 3 applesSTEP: I found 5 apples    When I found 5 apples
      STEP: I should have 8 apples    Then I should have 8 apples

      HOOK: before scenario  Scenario: Found more apples
      STEP: I have 3 applesSTEP: I found 2 apples    When I found 2 apples
      STEP: I should have 5 apples    Then I should have 5 apples

        Scenario Outline: Other situations
      HOOK: before scenarioSTEP: I have 3 applesSTEP: I ate 3 applesSTEP: I found 1 applesSTEP: I should have 1 apples    When I ate <ate> apples
          And I found <found> apples
          Then I should have <result> apples

          Examples:
            | ate | found | result |
            | 3   | 1     | 1      |
      HOOK: before scenarioSTEP: I have 3 applesSTEP: I ate 0 applesSTEP: I found 4 applesSTEP: I should have 8 apples      | 0   | 4     | 8      |
      HOOK: before scenarioSTEP: I have 3 applesSTEP: I ate 2 applesSTEP: I found 2 applesSTEP: I should have 3 apples      | 2   | 2     | 3      |

      6 scenarios (6 passed)
      21 steps (21 passed)
      """

  Scenario: Run feature with --dry-run
    When I run "behat --no-ansi --dry-run features/apples.feature"
    Then it should pass with:
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

        Scenario: Found more apples   # features/apples.feature:13
          When I found 5 apples       # FeatureContext::iFoundApples()
          Then I should have 8 apples # FeatureContext::iShouldHaveApples()

        Scenario: Found more apples   # features/apples.feature:17
          When I found 2 apples       # FeatureContext::iFoundApples()
          Then I should have 5 apples # FeatureContext::iShouldHaveApples()

        Scenario Outline: Other situations   # features/apples.feature:21
          When I ate <ate> apples            # FeatureContext::iAteApples()
          And I found <found> apples         # FeatureContext::iFoundApples()
          Then I should have <result> apples # FeatureContext::iShouldHaveApples()

          Examples:
            | ate | found | result |
            | 3   | 1     | 1      |
            | 0   | 4     | 8      |
            | 2   | 2     | 3      |

      6 scenarios (6 skipped)
      21 steps (21 skipped)
      """
