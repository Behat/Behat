Feature: Step Arguments Transformations
  In order to follow DRY
  As a feature writer
  I need to be able to move common
  arguments transformations
  into transformation functions

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
    And a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\ContextInterface,
          Behat\Behat\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeatureContext implements ContextInterface
      {
          private $user;

          /** @Transform /"([^\ "]+)(?: - (\d+))?" user/ */
          public function createUserFromUsername($username, $age = 20) {
              return new User($username, $age);
          }

          /** @Transform /^table:username,age$/ */
          public function createUserFromTable(TableNode $table) {
              $hash     = $table->getHash();
              $username = $hash[0]['username'];
              $age      = $hash[0]['age'];

              return new User($username, $age);
          }

          /** @Transform /^(\d+)$/ */
          public function castToNumber($number) {
              return intval($number);
          }

          /**
           * @Given /I am (".*" user)/
           * @Given /I am user:/
           */
          public function iAmUser(User $user) {
              $this->user = $user;
          }

          /**
           * @Then /Username must be "([^"]+)"/
           */
          public function usernameMustBe($username) {
              PHPUnit_Framework_Assert::assertEquals($username, $this->user->getUsername());
          }

          /**
           * @Then /Age must be (\d+)/
           */
          public function ageMustBe($age) {
              PHPUnit_Framework_Assert::assertEquals($age, $this->user->getAge());
              PHPUnit_Framework_Assert::assertInternalType('int', $age);
          }
      }
    """

  Scenario: Simple Arguments Transformations
    Given a file named "features/step_arguments.feature" with:
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
    When I run "behat -f progress --no-ansi"
    Then it should pass with:
      """
      ......

      2 scenarios (2 passed)
      6 steps (6 passed)
      """

  Scenario: Table Arguments Transformations
    Given a file named "features/table_arguments.feature" with:
      """
      Feature: Step Arguments
        Scenario:
          Given I am user:
            | username | age |
            | ever.zet | 22  |
          Then Username must be "ever.zet"
          And Age must be 22

        Scenario:
          Given I am user:
            | username | age |
            | vasiljev | 30  |
          Then Username must be "vasiljev"
          And Age must be 30
      """
    When I run "behat -f progress --no-ansi"
    Then it should pass with:
      """
      ......

      2 scenarios (2 passed)
      6 steps (6 passed)
      """

