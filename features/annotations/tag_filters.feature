Feature: Tags
  In order to run only needed features
  As a Behat user
  I need to Behat support features & scenario/outline tags

  Background:
    Given a file named "features/bootstrap/bootstrap.php" with:
      """
      <?php
      require_once 'PHPUnit/Autoload.php';
      require_once 'PHPUnit/Framework/Assert/Functions.php';
      """
    And a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\BehatContext,
          Behat\Behat\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeatureContext extends BehatContext
      {
          /**
           * @Given /^Some slow step N(\d+)$/
           */
          public function someSlowStepN($num) {}

          /**
           * @Given /^Some normal step N(\d+)$/
           */
          public function someNormalStepN($num) {}

          /**
           * @Given /^Some fast step N(\d+)$/
           */
          public function someFastStepN($num) {}
      }
    """
    And a file named "features/feature1.feature" with:
      """
      @slow
      Feature: Feature N1

        Background:
          Given Some slow step N11

        Scenario:
          Given Some slow step N12
          And Some normal step N13

        @fast
        Scenario:
          Given Some fast step N14
      """
    And a file named "features/feature2.feature" with:
      """
      Feature: Feature N2

        Background:
          Given Some normal step N21

        @slow @fast
        Scenario:
          Given Some slow step N22
          And Some fast step N23

        @fast
        Scenario:
          Given Some fast step N24
          And Some fast step N25
      """
    And a file named "features/feature3.feature" with:
      """
      Feature: Feature N3

        Background:
          Given Some normal step N21

        @slow
        Scenario Outline:
          Given Some slow step N<num>

          Examples:
            | num |
            | 31  |
            | 32  |

        @normal
        Scenario:
          Given Some normal step N38

        @fast
        Scenario Outline:
          Given Some fast step N<num>

          Examples:
            | num |
            | 33  |
            | 34  |

        @normal @fast
        Scenario Outline:
          Given Some normal step N<num>
          And Some fast step N37

          Examples:
            | num |
            | 35  |
            | 36  |
      """
    And a file named "features/feature4.feature" with:
      """
      Feature: Feature N4

        @normal
        Scenario:
          Given Some normal step N41
          And Some fast step N42

        @fast
        Scenario:
          Given Some slow step N43
      """

  Scenario: Single tag
    When I run "behat --no-ansi -f pretty --tags '@slow' --no-paths"
    Then it should pass
    And the output should contain:
      """
      @slow
      Feature: Feature N1

        Background:
          Given Some slow step N11

        Scenario:
          Given Some slow step N12
          And Some normal step N13

        @fast
        Scenario:
          Given Some fast step N14
      """
    And the output should contain:
      """
      Feature: Feature N2

        Background:
          Given Some normal step N21

        @slow @fast
        Scenario:
          Given Some slow step N22
          And Some fast step N23
      """
    And the output should contain:
      """
      Feature: Feature N3

        Background:
          Given Some normal step N21

        @slow
        Scenario Outline:
          Given Some slow step N<num>

          Examples:
            | num |
            | 31  |
            | 32  |
      """
    And the output should contain:
      """
      5 scenarios (5 passed)
      12 steps (12 passed)
      """

  Scenario: Or tags
    When I run "behat --no-ansi -f pretty --tags '@slow,@normal' --no-paths"
    Then it should pass
    And the output should contain:
      """
      @slow
      Feature: Feature N1

        Background:
          Given Some slow step N11

        Scenario:
          Given Some slow step N12
          And Some normal step N13

        @fast
        Scenario:
          Given Some fast step N14
      """
    And the output should contain:
      """
      Feature: Feature N2

        Background:
          Given Some normal step N21

        @slow @fast
        Scenario:
          Given Some slow step N22
          And Some fast step N23
      """
    And the output should contain:
      """
      Feature: Feature N3

        Background:
          Given Some normal step N21

        @slow
        Scenario Outline:
          Given Some slow step N<num>

          Examples:
            | num |
            | 31  |
            | 32  |

        @normal
        Scenario:
          Given Some normal step N38

        @normal @fast
        Scenario Outline:
          Given Some normal step N<num>
          And Some fast step N37

          Examples:
            | num |
            | 35  |
            | 36  |
      """
    And the output should contain:
      """
      Feature: Feature N4

        @normal
        Scenario:
          Given Some normal step N41
          And Some fast step N42
      """
    And the output should contain:
      """
      9 scenarios (9 passed)
      22 steps (22 passed)
      """

  Scenario: And tags
    When I run "behat --no-ansi -f pretty --tags '@slow,@normal&&@fast' --no-paths"
    Then it should pass
    And the output should contain:
      """
      @slow
      Feature: Feature N1

        Background:
          Given Some slow step N11

        @fast
        Scenario:
          Given Some fast step N14
      """
    And the output should contain:
      """
      Feature: Feature N2

        Background:
          Given Some normal step N21

        @slow @fast
        Scenario:
          Given Some slow step N22
          And Some fast step N23
      """
    And the output should contain:
      """
      Feature: Feature N3

        Background:
          Given Some normal step N21

        @normal @fast
        Scenario Outline:
          Given Some normal step N<num>
          And Some fast step N37

          Examples:
            | num |
            | 35  |
            | 36  |
      """
    And the output should contain:
      """
      4 scenarios (4 passed)
      11 steps (11 passed)
      """

  Scenario: Not tags
    When I run "behat --no-ansi -f pretty --tags '~@slow&&~@fast' --no-paths"
    Then it should pass
    And the output should contain:
      """
      Feature: Feature N3

        Background:
          Given Some normal step N21

        @normal
        Scenario:
          Given Some normal step N38
      """
    And the output should contain:
      """
      Feature: Feature N4

        @normal
        Scenario:
          Given Some normal step N41
          And Some fast step N42
      """
    And the output should contain:
      """
      2 scenarios (2 passed)
      4 steps (4 passed)
      """
