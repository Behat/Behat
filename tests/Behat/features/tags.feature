Feature: Tags
  In order to run only needed features
  As a Behat user
  I need to Behat support features & scenario/outline tags

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
      $steps->Given('/^I have entered (\d+)$/', function($world, $arg1) {
          $world->number = $arg1;
      });
      $steps->Then('/^I must have (\d+)$/', function($world, $arg1) {
          assertEquals($world->number, $arg1);
      });
      """

  Scenario: Feature tags
    Given a file named "features/feature1.feature" with:
      """
      @first-tag @wip @big
      Feature: First

        Background:
          Given I have entered 10

        Scenario:
          Then I must have 10

        Scenario:
          When I have entered 30
          Then I must have 30
      """
    Given a file named "features/feature2.feature" with:
      """
      @second-tag @big
      Feature: Second

        Background:
          Given I have entered 10

        Scenario:
          Then I must have 10

        Scenario:
          When I have entered 30
          Then I must have 30
          Then I must have 30
      """
    Given a file named "features/feature3.feature" with:
      """
      @third-tag @wip
      Feature: Second

        Scenario:
          Given I have entered 15
          When I have entered 15
      """
    When I run "behat -f progress --tags @wip"
    Then it should pass with:
      """
      .......

      3 scenarios (3 passed)
      7 steps (7 passed)
      """
    When I run "behat -f progress --tags @wip,@second-tag"
    Then it should pass with:
      """
      .............

      5 scenarios (5 passed)
      13 steps (13 passed)
      """
    When I run "behat -f progress --tags ~@big"
    Then it should pass with:
      """
      ..

      1 scenario (1 passed)
      2 steps (2 passed)
      """

  Scenario: Scenario tags
    Given a file named "features/feature1.feature" with:
      """
      Feature: First

        Background:
          Given I have entered 10

        @wip
        Scenario:
          Then I must have 10

        Scenario:
          When I have entered 30
          Then I must have 30
      """
    Given a file named "features/feature2.feature" with:
      """
      Feature: Second

        Background:
          Given I have entered 10

        Scenario:
          Then I must have 10

        @big
        Scenario:
          When I have entered 30
          Then I must have 30
          Then I must have 30
      """
    Given a file named "features/feature3.feature" with:
      """
      Feature: Second

        @wip
        Scenario:
          Given I have entered 15
          When I have entered 15
      """
    When I run "behat -f progress --tags @wip"
    Then it should pass with:
      """
      ....

      2 scenarios (2 passed)
      4 steps (4 passed)
      """
    When I run "behat -f progress --tags @big"
    Then it should pass with:
      """
      ....

      1 scenario (1 passed)
      4 steps (4 passed)
      """
    When I run "behat -f progress --tags ~@wip"
    Then it should pass with:
      """
      .........

      3 scenarios (3 passed)
      9 steps (9 passed)
      """
