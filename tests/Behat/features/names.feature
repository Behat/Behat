Feature: Names
  In order to run only needed features
  As a Behat user
  I need to Behat support features & scenario/outline names filtering

  Background:
    Given a standard Behat project directory structure
    And a file named "features/support/env.php" with:
      """
      <?php
      require_once 'PHPUnit/Autoload.php';
      require_once 'PHPUnit/Framework/Assert/Functions.php';
      """
    And a file named "features/steps/steps.php" with:
      """
      <?php
      $steps->Given('/^Some slow step #(\d+)$/', function($world, $num) {});
      $steps->Given('/^Some normal step #(\d+)$/', function($world, $num) {});
      $steps->Given('/^Some fast step #(\d+)$/', function($world, $num) {});
      """
    And a file named "features/feature1.feature" with:
      """
      Feature: First Feature

        Background:
          Given Some slow step #11

        Scenario: First Scenario
          Given Some slow step #12
          And Some normal step #13

        Scenario: Second Scenario
          Given Some fast step #14
      """
    And a file named "features/feature2.feature" with:
      """
      Feature: Second Feature

        Background:
          Given Some normal step #21

        Scenario: First Scenario
          Given Some slow step #22
          And Some fast step #23
      """

  Scenario: First Name
    When I run "behat -TCf pretty --name First"
    Then it should pass with:
      """
      Feature: First Feature
      
        Background:                # features/feature1.feature:3
          Given Some slow step #11 # features/steps/steps.php:2
      
        Scenario: First Scenario   # features/feature1.feature:6
          Given Some slow step #12 # features/steps/steps.php:2
          And Some normal step #13 # features/steps/steps.php:3
      
        Scenario: Second Scenario  # features/feature1.feature:10
          Given Some fast step #14 # features/steps/steps.php:4
      
      Feature: Second Feature
      
        Background:                  # features/feature2.feature:3
          Given Some normal step #21 # features/steps/steps.php:3
      
        Scenario: First Scenario     # features/feature2.feature:6
          Given Some slow step #22   # features/steps/steps.php:2
          And Some fast step #23     # features/steps/steps.php:4
      
      3 scenarios (3 passed)
      8 steps (8 passed)
      """

  Scenario: Second Name
    When I run "behat -TCf pretty --name 'Second Scenario'"
    Then it should pass with:
      """
      Feature: First Feature
      
        Background:                # features/feature1.feature:3
          Given Some slow step #11 # features/steps/steps.php:2
      
        Scenario: Second Scenario  # features/feature1.feature:10
          Given Some fast step #14 # features/steps/steps.php:4
      
      1 scenario (1 passed)
      2 steps (2 passed)
      """

  Scenario: RegEx
    When I run "behat -TCf pretty --name '/nd Scenario$/'"
    Then display last command output
    Then it should pass with:
      """
      Feature: First Feature
      
        Background:                # features/feature1.feature:3
          Given Some slow step #11 # features/steps/steps.php:2
      
        Scenario: Second Scenario  # features/feature1.feature:10
          Given Some fast step #14 # features/steps/steps.php:4
      
      1 scenario (1 passed)
      2 steps (2 passed)
      """
