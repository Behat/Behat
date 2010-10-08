Feature: Step Arguments Transformations
  In order to follow DRY
  As a feature writer
  I need to be able to move common
  arguments transformations
  into transformation functions

  Background:
    Given a standard Behat project directory structure
    And a file named "features/support/env.php" with:
      """
      <?php
      require_once 'PHPUnit/Autoload.php';
      require_once 'PHPUnit/Framework/Assert/Functions.php';
      """
    And a file named "features/steps/user.php" with:
      """
      <?php
      class User
      {
          private $username;

          public function __construct($username) { $this->username = $username; }
          public function getUsername() { return $this->username; }
      }

      $steps->Transform('/"([^"]+)" user/', function($username) {
          return new User($username);
      });

      $steps->Given('/I am ("\w+" user)/', function($world, $user) {
          assertInstanceOf('User', $user);
          $world->user = $user;
      });

      $steps->Then('/Username must be "([^"]+)"/', function($world, $username) {
          assertEquals($username, $world->user->getUsername());
      });
      """
    And a file named "features/World.feature" with:
      """
      Feature: Step Arguments
        Scenario:
          Given I am "everzet" user
          Then Username must be "everzet"

        Scenario:
          Given I am "antono" user
          Then Username must be "antono"
      """

  Scenario:
    When I run "behat -f progress"
    Then it should pass with:
      """
      ....
      
      2 scenarios (2 passed)
      4 steps (4 passed)
      """
