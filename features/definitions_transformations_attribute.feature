Feature: Step Arguments Transformations with Attributes
  In order to follow DRY
  As a feature writer
  I need to use transformation functions using PHP attributes

  Background:
    Given a file named "features/bootstrap/User.php" with:
      """
      <?php
      class User
      {
          private $username;
          private $age;

          public function __construct($username, $age = 20) {
              $this->username = $username;
              $this->age = $age;
          }

          public function getUsername() { return $this->username; }
          public function getAge() { return $this->age; }
      }
      """

  Scenario: Simple Arguments Transformations
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;
      use Behat\Step\Given;
      use Behat\Step\Then;
      use Behat\Transformation\Transform;
      use PHPUnit\Framework\Assert;

      class FeatureContext implements Context
      {
          private $user;

          #[Transform('/"([^\ "]+)(?: - (\d+))?" user/')]
          public function createUserFromUsername($username, $age = 20) {
              return new User($username, $age);
          }

          #[Given('/I am (".*" user)/')]
          public function iAmUser(User $user) {
              $this->user = $user;
          }

          #[Then('/Username must be "([^"]+)"/')]
          public function usernameMustBe($username) {
              Assert::assertEquals($username, $this->user->getUsername());
          }

           #[Then('/Age must be (\d+)/')]
          public function ageMustBe($age) {
              Assert::assertEquals($age, $this->user->getAge());
          }
      }
    """
    And a file named "features/step_arguments.feature" with:
      """
      Feature: Step Arguments
        Scenario:
          Given I am "everzet" user
          Then Username must be "everzet"
          And Age must be 20

        Scenario:
          Given I am "antono - 29" user
          Then Username must be "antono"
          And Age must be 29
      """
    When I run "behat -f progress --no-colors"
    Then it should pass with:
      """
      ......

      2 scenarios (2 passed)
      6 steps (6 passed)
      """

  Scenario: Transformation without parameters
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;
      use Behat\Step\Given;
      use Behat\Step\Then;
      use Behat\Transformation\Transform;
      use PHPUnit\Framework\Assert;

      class FeatureContext implements Context
      {
          private $user;

          #[Transform]
          public function userFromName($username) : User {
              return new User($username);
          }

          #[Given('I am :user')]
          public function iAm(User $user) {
              $this->user = $user;
          }

          #[Then('I should be a user named :name')]
          public function iShouldBeAUserNamed($username) {
              Assert::assertEquals($username, $this->user->getUserName());
          }
      }
      """
    And a file named "features/my.feature" with:
      """
      Feature:
        Scenario:
          Given I am "everzet"
          Then I should be a user named "everzet"
      """
    When I run "behat -f progress --no-colors"
    Then it should pass with:
      """
      ..

      1 scenario (1 passed)
      2 steps (2 passed)
      """

  Scenario: Multiple Transformations in one function
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;
      use Behat\Step\Given;
      use Behat\Step\Then;
      use Behat\Transformation\Transform;
      use PHPUnit\Framework\Assert;

      class FeatureContext implements Context
      {
          private $user;

          #[Transform('/"([^\ "]+)(?: - (\d+))?" user/')]
          #[Transform(':user')]
          public function createUserFromUsername($username, $age = 20) {
              return new User($username, $age);
          }

          #[Given('/I am (".*" user)/')]
          #[Given('I am :user')]
          public function iAmUser(User $user) {
              $this->user = $user;
          }

          #[Then('/Username must be "([^"]+)"/')]
          public function usernameMustBe($username) {
              Assert::assertEquals($username, $this->user->getUsername());
          }

          #[Then('/Age must be (\d+)/')]
          public function ageMustBe($age) {
              Assert::assertEquals($age, $this->user->getAge());
          }
      }
    """
    And a file named "features/step_arguments.feature" with:
      """
      Feature: Step Arguments
        Scenario:
          Given I am everzet
          Then Username must be "everzet"
          And Age must be 20

        Scenario:
          Given I am "antono - 29" user
          Then Username must be "antono"
          And Age must be 29
      """
    When I run "behat -f progress --no-colors"
    Then it should pass with:
      """
      ......

      2 scenarios (2 passed)
      6 steps (6 passed)
      """
