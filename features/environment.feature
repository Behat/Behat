Feature: Environment consistency
  In order to maintain stable behavior tests
  As a feature writer
  I need a separate environment for every scenario/outline

  Background:
    Given a file named "features/support/bootstrap.php" with:
      """
      <?php
      require_once 'PHPUnit/Autoload.php';
      require_once 'PHPUnit/Framework/Assert/Functions.php';
      """
    And a file named "features/steps/apple_steps.php" with:
      """
      <?php
      $steps->Given('/^I have (\d+) apples?$/', function($world, $apples) {
          $world->apples = intval($apples);
      });
      $steps->When('/^I ate (\d+) apples?$/', function($world, $apples) {
          $world->apples -= intval($apples);
      });
      $steps->When('/^I found (\d+) apples?$/', function($world, $apples) {
          $world->apples += intval($apples);
      });
      $steps->Then('/^I should have (\d+) apples$/', function($world, $apples) {
          assertEquals(intval($apples), $world->apples);
      });
      """

  Scenario: True "apples story"
    Given a file named "features/apples.feature" with:
      """
      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Background:
          Given I have 3 apples

        Scenario: I'm little hungry
          When I ate 1 apple
          Then I should have 2 apples

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
            | 0   | 5     | 8      |
            | 2   | 2     | 3      |
      """
    When I run "behat -TCf progress features/apples.feature"
    Then it should pass with:
      """
      ..................
      
      5 scenarios (5 passed)
      18 steps (18 passed)
      """

  Scenario: False "apples story"
    Given a file named "features/apples.feature" with:
      """
      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Background:
          Given I have 3 apples

        Scenario: I'm little hungry
          When I ate 1 apple
          Then I should have 5 apples

        Scenario: Found more apples
          When I found 10 apples
          Then I should have 10 apples

        Scenario Outline: Other situations
          When I ate <ate> apples
          And I found <found> apples
          Then I should have <result> apples

          Examples:
            | ate | found | result |
            | 3   | 1     | 3      |
            | 0   | 5     | 8      |
            | 2   | 2     | 4      |
      """
    When I run "behat -TCf progress features/apples.feature"
    Then it should fail with:
      """
      ..F..F...F.......F
      
      (::) failed steps (::)
      
      01. Failed asserting that <integer:2> is equal to <integer:5>.
          In step `Then I should have 5 apples'. # features/steps/apple_steps.php:13
          From scenario `I'm little hungry'.     # features/apples.feature:9
      
      02. Failed asserting that <integer:13> is equal to <integer:10>.
          In step `Then I should have 10 apples'. # features/steps/apple_steps.php:13
          From scenario `Found more apples'.      # features/apples.feature:13
      
      03. Failed asserting that <integer:1> is equal to <integer:3>.
          In step `Then I should have 4 apples'.  # features/steps/apple_steps.php:13
          From scenario `Other situations'.       # features/apples.feature:17
      
      04. Failed asserting that <integer:3> is equal to <integer:4>.
          In step `Then I should have 4 apples'.  # features/steps/apple_steps.php:13
          From scenario `Other situations'.       # features/apples.feature:17
      
      5 scenarios (1 passed, 4 failed)
      18 steps (14 passed, 4 failed)
      """
