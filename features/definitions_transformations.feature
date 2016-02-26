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

      use Behat\Behat\Context\Context,
          Behat\Behat\Tester\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeatureContext implements Context
      {
          private $user;

          /** @Transform /"([^\ "]+)(?: - (\d+))?" user/ */
          public function createUserFromUsername($username, $age = 20) {
              return new User($username, $age);
          }

          /** @Transform table:username,age */
          public function createUserFromTable(TableNode $table) {
              $hash     = $table->getHash();
              $username = $hash[0]['username'];
              $age      = $hash[0]['age'];

              return new User($username, $age);
          }

          /** @Transform rowtable:username,age */
          public function createUserFromRowTable(TableNode $table) {
              $hash     = $table->getRowsHash();
              $username = $hash['username'];
              $age      = $hash['age'];

              return new User($username, $age);
          }

          /** @Transform row:username */
          public function createUserNamesFromTable($tableRow) {
              return $tableRow['username'];
          }

          /** @Transform /^\d+$/ */
          public function castToNumber($number) {
              return intval($number);
          }

          /** @Transform :user */
          public function castToUser($username) {
              return new User($username);
          }

          /**
           * @Transform /^(yes|no)$/
           */
          public function castEinenOrKeinenToBoolean($expected) {
              return 'yes' === $expected;
          }

          /**
           * @Given /I am (".*" user)/
           * @Given I am user:
           * @Given I am :user
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

          /**
           * @Then the Usernames must be:
           */
          public function usernamesMustBe(array $usernames) {
              PHPUnit_Framework_Assert::assertEquals($usernames[0], $this->user->getUsername());
          }

          /**
           * @Then /^the boolean (no) should be transformed to false$/
           */
          public function theBooleanShouldBeTransformed($boolean) {
              PHPUnit_Framework_Assert::assertSame(false, $boolean);
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
          And the boolean no should be transformed to false

        Scenario:
          Given I am "antono - 29" user
          Then Username must be "antono"
          And Age must be 29
      """
    When I run "behat -f progress --no-colors"
    Then it should pass with:
      """
      .......

      2 scenarios (2 passed)
      7 steps (7 passed)
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
    When I run "behat -f progress --no-colors"
    Then it should pass with:
      """
      ......

      2 scenarios (2 passed)
      6 steps (6 passed)
      """
  Scenario: Row Table Arguments Transformations
    Given a file named "features/row_table_arguments.feature" with:
      """
      Feature: Step Arguments
        Scenario:
          Given I am user:
            | username | ever.zet |
            | age      | 22       |
          Then Username must be "ever.zet"
          And Age must be 22

        Scenario:
          Given I am user:
            | username | vasiljev |
            | age      | 30       |
          Then Username must be "vasiljev"
          And Age must be 30
      """
    When I run "behat -f progress --no-colors"
    Then it should pass with:
      """
      ......

      2 scenarios (2 passed)
      6 steps (6 passed)
      """
  Scenario: Table Row Arguments Transformations
    Given a file named "features/table_row_arguments.feature" with:
      """
      Feature: Step Arguments
        Scenario:
          Given I am user:
            | username | age |
            | ever.zet | 22  |
          Then the Usernames must be:
            | username |
            | ever.zet |
      """
    When I run "behat -f progress --no-colors"
    Then it should pass with:
      """
      ..

      1 scenario (1 passed)
      2 steps (2 passed)
      """

  Scenario: Named Arguments Transformations
    Given a file named "features/step_arguments.feature" with:
      """
      Feature: Step Arguments
        Scenario:
          Given I am "everzet"
          Then Username must be "everzet"

        Scenario:
          Given I am "antono"
          Then Username must be "antono"
      """
    When I run "behat -f progress --no-colors"
    Then it should pass with:
      """
      ....

      2 scenarios (2 passed)
      4 steps (4 passed)
      """

  Scenario: Transforming different types
    Given a file named "features/to_null.feature" with:
      """
      Feature: I should be able to transform values into different types for testing

      Scenario Outline: Converting different types
        Given I have the value "<value>"
        Then it should be of type "<type>"

        Examples:
            | value      | type       |
            | "soeuhtou" | string     |
            | 34         | integer    |
            | null       | NULL       |
      """
    And a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      class FeatureContext implements Behat\Behat\Context\Context
      {
          public function __construct()
          {
              unset($this->value);
          }

          /**
           * @Transform /^".*"$/
           */
          public function transformString($string)
          {
              return strval($string);
          }

          /**
           * @Transform /^\d+$/
           */
          public function transformInt($int)
          {
              return intval($int);
          }

          /**
           * @Transform /^null/
           */
          public function transformNull($null)
          {
              return null;
          }

          /**
           * @Given I have the value ":value"
           */
          public function iHaveTheValue($value)
          {
              $this->value = $value;
          }

          /**
           * @Then it should be of type :type
           */
          public function itShouldBeOfType($type)
          {
              if (gettype($this->value) != $type) {
                  throw new Exception("Expected " . $type . ", got " . gettype($this->value) . " (value: " . $this->value . ")");
              }
          }
      }
      """
    When I run "behat -f progress --no-colors"
    Then it should pass with:
      """
      ......

      3 scenarios (3 passed)
      6 steps (6 passed)
      """
