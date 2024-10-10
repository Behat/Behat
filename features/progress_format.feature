Feature: Progress format

  In order to show large test results rapidly, the progress formatter uses a dot notation to use very little terminal space

  Scenario: Failing scenarios are listed at the end of output
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context,
          Behat\Behat\Tester\Exception\PendingException;

      class FeatureContext implements Context
      {
          /**
           * @Given a failing step
           */
          public function fail() {
              throw new \Exception('oops');
          }
      }
      """
    And a file named "features/World.feature" with:
      """
      Feature:
        Scenario: Undefined
          Given a failing step
      """
    When I run "behat --format=progress"
    Then it should fail with:
      """
      --- Failed scenarios:

          features/World.feature:2
      """
