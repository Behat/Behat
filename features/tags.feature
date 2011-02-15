Feature: Tags
  In order to run only needed features
  As a Behat user
  I need to Behat support features & scenario/outline tags

  Background:
    Given a file named "features/support/bootstrap.php" with:
      """
      <?php
      require_once 'PHPUnit/Autoload.php';
      require_once 'PHPUnit/Framework/Assert/Functions.php';
      """
    And a file named "features/steps/steps.php" with:
      """
      <?php
      $steps->Given('/^Some slow step N(\d+)$/', function($world, $num) {});
      $steps->Given('/^Some normal step N(\d+)$/', function($world, $num) {});
      $steps->Given('/^Some fast step N(\d+)$/', function($world, $num) {});
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
    When I run "behat -TCf pretty --tags @slow"
    Then it should pass with:
      """
      @slow
      Feature: Feature N1
      
        Background:                # features/feature1.feature:4
          Given Some slow step N11 # features/steps/steps.php:2
      
        Scenario:                  # features/feature1.feature:7
          Given Some slow step N12 # features/steps/steps.php:2
          And Some normal step N13 # features/steps/steps.php:3
      
        @fast
        Scenario:                  # features/feature1.feature:12
          Given Some fast step N14 # features/steps/steps.php:4
      
      Feature: Feature N2
      
        Background:                  # features/feature2.feature:3
          Given Some normal step N21 # features/steps/steps.php:3
      
        @slow @fast
        Scenario:                    # features/feature2.feature:7
          Given Some slow step N22   # features/steps/steps.php:2
          And Some fast step N23     # features/steps/steps.php:4
      
      Feature: Feature N3
      
        Background:                  # features/feature3.feature:3
          Given Some normal step N21 # features/steps/steps.php:3
      
        @slow
        Scenario Outline:             # features/feature3.feature:7
          Given Some slow step N<num> # features/steps/steps.php:2
      
          Examples:
            | num |
            | 31  |
            | 32  |
      
      5 scenarios (5 passed)
      12 steps (12 passed)
      """

  Scenario: Or tags
    When I run "behat -TCf pretty --tags @slow,@normal"
    Then it should pass with:
      """
      @slow
      Feature: Feature N1
      
        Background:                # features/feature1.feature:4
          Given Some slow step N11 # features/steps/steps.php:2
      
        Scenario:                  # features/feature1.feature:7
          Given Some slow step N12 # features/steps/steps.php:2
          And Some normal step N13 # features/steps/steps.php:3
      
        @fast
        Scenario:                  # features/feature1.feature:12
          Given Some fast step N14 # features/steps/steps.php:4
      
      Feature: Feature N2
      
        Background:                  # features/feature2.feature:3
          Given Some normal step N21 # features/steps/steps.php:3
      
        @slow @fast
        Scenario:                    # features/feature2.feature:7
          Given Some slow step N22   # features/steps/steps.php:2
          And Some fast step N23     # features/steps/steps.php:4
      
      Feature: Feature N3
      
        Background:                  # features/feature3.feature:3
          Given Some normal step N21 # features/steps/steps.php:3
      
        @slow
        Scenario Outline:             # features/feature3.feature:7
          Given Some slow step N<num> # features/steps/steps.php:2
      
          Examples:
            | num |
            | 31  |
            | 32  |
      
        @normal
        Scenario:                     # features/feature3.feature:16
          Given Some normal step N38  # features/steps/steps.php:3
      
        @normal @fast
        Scenario Outline:               # features/feature3.feature:29
          Given Some normal step N<num> # features/steps/steps.php:3
          And Some fast step N37        # features/steps/steps.php:4
      
          Examples:
            | num |
            | 35  |
            | 36  |
      
      Feature: Feature N4
      
        @normal
        Scenario:                       # features/feature4.feature:4
          Given Some normal step N41    # features/steps/steps.php:3
          And Some fast step N42        # features/steps/steps.php:4
      
      9 scenarios (9 passed)
      22 steps (22 passed)
      """

  Scenario: And tags
    When I run "behat -TCf pretty --tags '@slow,@normal&&@fast'"
    Then it should pass with:
      """
      @slow
      Feature: Feature N1
      
        Background:                # features/feature1.feature:4
          Given Some slow step N11 # features/steps/steps.php:2
      
        @fast
        Scenario:                  # features/feature1.feature:12
          Given Some fast step N14 # features/steps/steps.php:4
      
      Feature: Feature N2
      
        Background:                  # features/feature2.feature:3
          Given Some normal step N21 # features/steps/steps.php:3
      
        @slow @fast
        Scenario:                    # features/feature2.feature:7
          Given Some slow step N22   # features/steps/steps.php:2
          And Some fast step N23     # features/steps/steps.php:4
      
      Feature: Feature N3
      
        Background:                  # features/feature3.feature:3
          Given Some normal step N21 # features/steps/steps.php:3
      
        @normal @fast
        Scenario Outline:               # features/feature3.feature:29
          Given Some normal step N<num> # features/steps/steps.php:3
          And Some fast step N37        # features/steps/steps.php:4
      
          Examples:
            | num |
            | 35  |
            | 36  |
      
      4 scenarios (4 passed)
      11 steps (11 passed)
      """

  Scenario: Not tags
    When I run "behat -TCf pretty --tags '~@slow&&~@fast'"
    Then it should pass with:
      """
      Feature: Feature N3
      
        Background:                  # features/feature3.feature:3
          Given Some normal step N21 # features/steps/steps.php:3
      
        @normal
        Scenario:                    # features/feature3.feature:16
          Given Some normal step N38 # features/steps/steps.php:3
      
      Feature: Feature N4
      
        @normal
        Scenario:                    # features/feature4.feature:4
          Given Some normal step N41 # features/steps/steps.php:3
          And Some fast step N42     # features/steps/steps.php:4
      
      2 scenarios (2 passed)
      4 steps (4 passed)
      """
