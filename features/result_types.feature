Feature: Different result types
  In order to differentiate feature statuses
  As a feature writer
  I need to be able to see different types of test results

  Background:
    Given I initialise the working directory from the "ResultTypes" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value    |
      | --no-colors |          |
      | --format    | progress |

  Scenario: Undefined steps
    When I run "behat --profile=undefined features/undefined.feature --snippets-for=UndefinedContext --snippets-type=regex"
    Then it should pass with:
      """
      UUUUUU

      2 scenarios (2 undefined)
      6 steps (6 undefined)

      --- UndefinedContext has missing steps. Define them with these snippets:

          #[Given('/^I have magically created (\d+)\$$/')]
          public function iHaveMagicallyCreated($arg1): void
          {
              throw new PendingException();
          }

          #[When('/^I have chose "([^"]*)" in coffee machine$/')]
          public function iHaveChoseInCoffeeMachine($arg1): void
          {
              throw new PendingException();
          }

          #[Then('/^I should have "([^"]*)"$/')]
          public function iShouldHave($arg1): void
          {
              throw new PendingException();
          }

      --- Don't forget these 4 use statements:

          use Behat\Behat\Tester\Exception\PendingException;
          use Behat\Step\Given;
          use Behat\Step\When;
          use Behat\Step\Then;
      """
    When I run "behat --strict --profile=undefined features/undefined.feature --snippets-for=UndefinedContext --snippets-type=regex"
    Then it should fail with:
      """
      UUUUUU

      2 scenarios (2 undefined)
      6 steps (6 undefined)

      --- UndefinedContext has missing steps. Define them with these snippets:

          #[Given('/^I have magically created (\d+)\$$/')]
          public function iHaveMagicallyCreated($arg1): void
          {
              throw new PendingException();
          }

          #[When('/^I have chose "([^"]*)" in coffee machine$/')]
          public function iHaveChoseInCoffeeMachine($arg1): void
          {
              throw new PendingException();
          }

          #[Then('/^I should have "([^"]*)"$/')]
          public function iShouldHave($arg1): void
          {
              throw new PendingException();
          }

      --- Don't forget these 4 use statements:

          use Behat\Behat\Tester\Exception\PendingException;
          use Behat\Step\Given;
          use Behat\Step\When;
          use Behat\Step\Then;
      """

  Scenario: Pending steps
    When I run "behat --profile=pending features/pending.feature --snippets-for=PendingContext --snippets-type=regex"
    Then it should pass with:
      """
      P-U

      --- Pending steps:

      001 Scenario: When the coffee ready                        # features/pending.feature:9
            Given human have ordered very very very hot "coffee" # PendingContext::humanOrdered()
              TODO: write pending definition

      1 scenario (1 undefined)
      3 steps (1 undefined, 1 pending, 1 skipped)

      --- PendingContext has missing steps. Define them with these snippets:

          #[Then('/^I should say "([^"]*)"$/')]
          public function iShouldSay($arg1): void
          {
              throw new PendingException();
          }

      --- Don't forget these 2 use statements:

          use Behat\Behat\Tester\Exception\PendingException;
          use Behat\Step\Then;
      """
    When I run "behat --strict --profile=pending features/pending.feature --snippets-for=PendingContext --snippets-type=regex"
    Then it should fail with:
      """
      P-U

      --- Pending steps:

      001 Scenario: When the coffee ready                        # features/pending.feature:9
            Given human have ordered very very very hot "coffee" # PendingContext::humanOrdered()
              TODO: write pending definition

      1 scenario (1 undefined)
      3 steps (1 undefined, 1 pending, 1 skipped)

      --- PendingContext has missing steps. Define them with these snippets:

          #[Then('/^I should say "([^"]*)"$/')]
          public function iShouldSay($arg1): void
          {
              throw new PendingException();
          }
      """

  Scenario: Failed steps
    When I run "behat --profile=failed features/failed.feature"
    Then it should fail with:
      """
      .F..F-

      --- Failed steps:

      001 Scenario: Check thrown amount         # features/failed.feature:9
            Then I should see 12$ on the screen # features/failed.feature:10
              Failed asserting that 10 matches expected '12'.

      002 Scenario: Additional throws           # features/failed.feature:12
            Then I should see 31$ on the screen # features/failed.feature:14
              Failed asserting that 30 matches expected '31'.

      2 scenarios (2 failed)
      6 steps (3 passed, 2 failed, 1 skipped)
      """

  Scenario: Skipped steps
    When I run "behat --profile=skipped features/skipped.feature"
    Then it should fail with:
      """
      .F---..F--

      --- Failed steps:

      001 Scenario: I have no water # features/skipped.feature:9
            Given I have no water   # features/skipped.feature:10
              NO water in coffee machine!!! (Exception)

      002 Scenario: I have no electricity # features/skipped.feature:15
            And I have no electricity     # features/skipped.feature:17
              NO electricity in coffee machine!!! (Exception)

      2 scenarios (2 failed)
      10 steps (3 passed, 2 failed, 5 skipped)
      """

  Scenario: Ambiguous steps
    When I run "behat --profile=ambiguous features/ambiguous.feature"
    Then it should fail with:
      """
      F-

      --- Failed steps:

      001 Scenario: Ambiguous coffee type   # features/ambiguous.feature:6
            Given human have chosen "Latte" # features/ambiguous.feature:7
              Ambiguous match of "human have chosen "Latte"":
              to `/^human have chosen "([^"]*)"$/` from AmbiguousContext::chosen()
              to `/^human have chosen "Latte"$/` from AmbiguousContext::chosenLatte()

      1 scenario (1 failed)
      2 steps (1 failed, 1 skipped)
      """

  Scenario: Redundant steps
    When I run "behat --profile=redundant features/redundant.feature"
    Then it should fail
    And the output should contain:
      """
      Step "/^customer bought coffee$/" is already defined in RedundantContext::chosen()
      """

  Scenario: Warning-containing steps
    When I run "behat --profile=warning features/warning.feature"
    Then it should fail
    And the output should contain:
      """
      F-

      --- Failed steps:

      001 Scenario: Redundant menu       # features/warning.feature:6
            Given customer bought coffee # features/warning.feature:7
              User Warning: some warning in features/bootstrap/WarningContext.php line 11

      1 scenario (1 failed)
      2 steps (1 failed, 1 skipped)
      """
