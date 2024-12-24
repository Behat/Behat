Feature: Rerun with strict
  In order to test only failed scenarios with the strict option
  As a feature developer
  I need to have an ability to rerun failed previously scenarios, including those which failed due to the strict option

  Background:
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;
      use Behat\Step\When;

      class FeatureContext implements Context
      {
          #[When('I have a failing step')]
          public function iHaveAFailingStep(): void
          {
              throw new \Exception();
          }

          #[When('I have a passing step')]
          public function iHaveAPassingStep(): void
          {
          }
      }
      """
    And a file named "features/rerun_strict.feature" with:
      """
      Feature: test

        Scenario: missing step
          When I have a missing step

        Scenario: failing step
          When I have a failing step

        Scenario: passing step
          When I have a passing step
      """

  Scenario: Rerun feature without strict option
    When I run "behat --no-colors -f progress -n features/rerun_strict.feature"
    And I run "behat  --rerun --no-colors -f progress -n features/rerun_strict.feature"
    Then it should fail with:
      """
      F

      --- Failed steps:

      001 Scenario: failing step       # features/rerun_strict.feature:6
            When I have a failing step # features/rerun_strict.feature:7
              (Exception)

      1 scenario (1 failed)
      1 step (1 failed)
      """

    Scenario: Rerun feature with strict option
        When I run "behat --strict --no-colors -f progress -n features/rerun_strict.feature"
        And I run "behat --strict --rerun --no-colors -f progress --rerun -n features/rerun_strict.feature"
        Then it should fail with:
      """
      UF

      --- Failed steps:

      001 Scenario: failing step       # features/rerun_strict.feature:6
            When I have a failing step # features/rerun_strict.feature:7
              (Exception)

      2 scenarios (1 failed, 1 undefined)
      2 steps (1 failed, 1 undefined)
      """
