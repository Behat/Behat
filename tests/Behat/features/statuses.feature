Feature: Statuses
  In order to run test suites
  As a Behat user
  I need to Behat return proper status messages

  Background:
    Given a standard Behat project directory structure
    And a file named "features/support/env.php" with:
      """
      <?php
      require_once 'PHPUnit/Autoload.php';
      require_once 'PHPUnit/Framework/Assert/Functions.php';
      """

  Scenario: Undefined steps
    Given a file named "features/statuses.feature" with:
      """
      Feature: Undefined

        Background:
          Given I have entered 10

        Scenario:
          Then I must have 10
          Then String must be 'string'

        Scenario:
          When I have entered 30
          Then I must have 30
      """
    When I run "behat -TCf progress features/statuses.feature"
    Then it should fail with:
      """
      UUUUUU

      2 scenarios (2 undefined)
      6 steps (6 undefined)

      You can implement step definitions for undefined steps with these snippets:

      $steps->Given('/^I have entered (\d+)$/', function($world, $arg1) {
          throw new \Behat\Behat\Exception\Pending();
      });

      $steps->Then('/^I must have (\d+)$/', function($world, $arg1) {
          throw new \Behat\Behat\Exception\Pending();
      });

      $steps->Then('/^String must be \'([^\']*)\'$/', function($world, $arg1) {
          throw new \Behat\Behat\Exception\Pending();
      });
      """

  Scenario: Pending steps
    Given a file named "features/statuses.feature" with:
      """
      Feature: Pending

        Background:
          Given I have entered 10

        Scenario:
          Then I must have 10

        Scenario:
          When I have entered 30
          Then I must have 30
      """
    And a file named "features/steps/steps.php" with:
      """
      <?php
      $steps->Given('/^I have entered (\d+)$/', function($world, $arg1) {
          throw new \Behat\Behat\Exception\Pending();
      });
      """
    When I run "behat -TCf progress features/statuses.feature"
    Then it should fail with:
      """
      PUP-U

      (::) pending steps (::)

      01. TODO: write pending definition
          In step `Given I have entered 10'. # features/steps/steps.php:4
          From scenario background.          # features/statuses.feature:3

      02. TODO: write pending definition
          In step `Given I have entered 10'. # features/steps/steps.php:4
          From scenario background.          # features/statuses.feature:3

      2 scenarios (2 undefined)
      5 steps (1 skipped, 2 pending, 2 undefined)

      You can implement step definitions for undefined steps with these snippets:

      $steps->Then('/^I must have (\d+)$/', function($world, $arg1) {
          throw new \Behat\Behat\Exception\Pending();
      });
      """

  Scenario: Failed
    Given a file named "features/statuses.feature" with:
      """
      Feature: Failed

        Background:
          Given I have entered 10

        Scenario:
          Then I must have 12

        Scenario:
          When I have entered 30
          Then I must have 30
          And I must have 31
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
    When I run "behat -TCf progress features/statuses.feature"
    Then it should fail with:
      """
      .F...F

      (::) failed steps (::)

      01. Failed asserting that <string:12> is equal to <string:10>.
          In step `Then I must have 12'. # features/steps/steps.php:8
          From scenario ***.             # features/statuses.feature:6

      02. Failed asserting that <string:31> is equal to <string:30>.
          In step `And I must have 31'.  # features/steps/steps.php:8
          From scenario ***.             # features/statuses.feature:9

      2 scenarios (2 failed)
      6 steps (4 passed, 2 failed)
      """

  Scenario: Skipped
    Given a file named "features/statuses.feature" with:
      """
      Feature: Skipped

        Background:
          Given I have entered 10

        Scenario:
          Then I must have 12

        Scenario:
          When I have entered 30
          Then I must have 30
          And I must have 31
          And I must have 20
          And I must have 50
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
    When I run "behat -TCf progress features/statuses.feature"
    Then it should fail with:
      """
      .F...F--

      (::) failed steps (::)

      01. Failed asserting that <string:12> is equal to <string:10>.
          In step `Then I must have 12'. # features/steps/steps.php:8
          From scenario ***.             # features/statuses.feature:6

      02. Failed asserting that <string:31> is equal to <string:30>.
          In step `And I must have 31'.  # features/steps/steps.php:8
          From scenario ***.             # features/statuses.feature:9

      2 scenarios (2 failed)
      8 steps (4 passed, 2 skipped, 2 failed)
      """

  Scenario: Ambiguous steps
    Given a file named "features/statuses.feature" with:
      """
      Feature: Pending

        Background:
          Given I have entered 10

        Scenario:
          Then I must have 10

        Scenario:
          When I have entered 30
          Then I must have 30
      """
    And a file named "features/steps/steps.php" with:
      """
      <?php
      $steps->Given('/^I have entered (\d+)$/', function($world, $arg1) {
          throw new \Behat\Behat\Exception\Pending();
      });
      $steps->Given('/^I have entered 10$/', function($world, $arg1) {
          assertTrue(true);
      });
      """
    When I run "behat -TCf progress features/statuses.feature"
    Then it should fail with:
      """
      FUF-U
      
      (::) failed steps (::)
      
      01. Ambiguous match of "I have entered 10":
          features/steps/steps.php:4:in `/^I have entered (\d+)$/`
          features/steps/steps.php:7:in `/^I have entered 10$/`
          In step `Given I have entered 10'.
          From scenario background.          # features/statuses.feature:3
      
      02. Ambiguous match of "I have entered 10":
          features/steps/steps.php:4:in `/^I have entered (\d+)$/`
          features/steps/steps.php:7:in `/^I have entered 10$/`
          In step `Given I have entered 10'.
          From scenario background.          # features/statuses.feature:3
      
      2 scenarios (2 failed)
      5 steps (1 skipped, 2 undefined, 2 failed)
      
      You can implement step definitions for undefined steps with these snippets:
      
      $steps->Then('/^I must have (\d+)$/', function($world, $arg1) {
          throw new \Behat\Behat\Exception\Pending();
      });
      """

  Scenario: Redundant steps
    Given a file named "features/statuses.feature" with:
      """
      Feature: Pending

        Background:
          Given I have entered 10

        Scenario:
          Then I must have 10

        Scenario:
          When I have entered 30
          Then I must have 30
      """
    And a file named "features/steps/steps.php" with:
      """
      <?php
      $steps->Given('/^I have entered (\d+)$/', function($world, $arg1) {
          throw new \Behat\Behat\Exception\Pending();
      });
      $steps->Given('/^I have entered (\d+)$/', function($world, $arg1) {
          assertTrue(true);
      });
      """
    When I run "behat -TCf progress features/statuses.feature"
    Then it should fail
