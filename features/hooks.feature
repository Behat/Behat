Feature: hooks
  In order to hook into Behat testing process
  As a tester
  I need to be able to write hooks

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
      $steps->Given('/^I have entered (\d+)$/', function($world, $arg1) {
          $world->number = $arg1;
      });
      $steps->Then('/^I must have (\d+)$/', function($world, $arg1) {
          assertEquals($world->number, $arg1);
      });
      """
    And a file named "features/support/hooks.php" with:
      """
      <?php
      $hooks->beforeSuite(function($event) {
          echo "= do something before all suite run\n";
      });
      $hooks->afterSuite(function($event) {
          echo "= do something after all suite run\n";
      });
      $hooks->beforeScenario('', function($event) {
          $env = $event->get('environment');
          $env->number = 50;
      });
      $hooks->beforeScenario('130', function($event) {
          $env = $event->get('environment');
          $env->number = 130;
      });
      $hooks->beforeScenario('@thirty', function($event) {
          $env = $event->get('environment');
          $env->number = 30;
      });
      $hooks->afterStep('@100', function($event) {
          $env = $event->get('environment');
          $env->number = 100;
      });
      """

  Scenario:
    Given a file named "features/test.feature" with:
      """
      Feature:
        Scenario:
          Then I must have 50
        Scenario:
          Given I have entered 12
          Then I must have 12

        @thirty
        Scenario:
          Given I must have 30
          When I have entered 23
          Then I must have 23
        @100 @thirty
        Scenario:
          Given I must have 30
          When I have entered 1
          Then I must have 100

        Scenario: 130
          Given I must have 130
      """
    When I run "behat -TCf pretty"
    Then it should pass with:
      """
      = do something before all suite run
      Feature:
      
        Scenario:             # features/test.feature:2
          Then I must have 50 # features/steps/steps.php:7
      
        Scenario:                 # features/test.feature:4
          Given I have entered 12 # features/steps/steps.php:4
          Then I must have 12     # features/steps/steps.php:7
      
        @thirty
        Scenario:                 # features/test.feature:9
          Given I must have 30    # features/steps/steps.php:7
          When I have entered 23  # features/steps/steps.php:4
          Then I must have 23     # features/steps/steps.php:7
      
        @100 @thirty
        Scenario:                 # features/test.feature:14
          Given I must have 30    # features/steps/steps.php:7
          When I have entered 1   # features/steps/steps.php:4
          Then I must have 100    # features/steps/steps.php:7
      
        Scenario: 130             # features/test.feature:19
          Given I must have 130   # features/steps/steps.php:7
      
      = do something after all suite run
      5 scenarios (5 passed)
      10 steps (10 passed)
      """
