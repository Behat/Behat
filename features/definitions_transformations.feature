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

          /** @Transform table:%username@,age# */
          public function createUserFromTableWithSymbol(TableNode $table) {
              $hash     = $table->getHash();
              $username = $hash[0]['%username@'];
              $age      = $hash[0]['age#'];

              return new User($username, $age);
          }

          /** @Transform rowtable:--username,age */
          public function createUserFromRowTableWithSymbol(TableNode $table) {
              $hash     = $table->getRowsHash();
              $username = $hash['--username'];
              $age      = $hash['age'];

              return new User($username, $age);
          }

          /** @Transform row:$username */
          public function createUserNamesFromTableWithSymbol($tableRow) {
              return $tableRow['$username'];
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
              PHPUnit\Framework\Assert::assertEquals($username, $this->user->getUsername());
          }

          /**
           * @Then /Age must be (\d+)/
           */
          public function ageMustBe($age) {
              PHPUnit\Framework\Assert::assertEquals($age, $this->user->getAge());
              PHPUnit\Framework\Assert::assertIsInt($age);
          }

          /**
           * @Then the Usernames must be:
           */
          public function usernamesMustBe(array $usernames) {
              PHPUnit\Framework\Assert::assertEquals($usernames[0], $this->user->getUsername());
          }

          /**
           * @Then /^the boolean (no) should be transformed to false$/
           */
          public function theBooleanShouldBeTransformed($boolean) {
              PHPUnit\Framework\Assert::assertSame(false, $boolean);
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

        Scenario:
          Given I am user:
            | %username@ | age# |
            | rajesh     | 35  |
          Then Username must be "rajesh"
          And Age must be 35
      """
    When I run "behat -f progress --no-colors"
    Then it should pass with:
      """
      ......

      3 scenarios (3 passed)
      9 steps (9 passed)
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

        Scenario:
          Given I am user:
            | --username | rajesh |
            | age        | 35     |
          Then Username must be "rajesh"
          And Age must be 35
      """
    When I run "behat -f progress --no-colors"
    Then it should pass with:
      """
      ......

      3 scenarios (3 passed)
      9 steps (9 passed)
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

        Scenario:
          Given I am user:
            | %username@ | age# |
            | rajesh     | 35   |
          Then the Usernames must be:
            | $username |
            | rajesh    |
      """
    When I run "behat -f progress --no-colors"
    Then it should pass with:
      """
      ....

      2 scenarios (2 passed)
      4 steps (4 passed)
      """

  Scenario: Whole table transformation
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;
      use Behat\Gherkin\Node\TableNode;

      class FeatureContext implements Context
      {
          private $data;

          /** @Transform table:* */
          public function transformTable(TableNode $table) {
              return $table->getHash();
          }

          /** @Given data: */
          public function givenData(array $data) {
              $this->data = $data;
          }

          /** @Then the :field should be :value */
          public function theFieldShouldBe($field, $value) {
              PHPUnit\Framework\Assert::assertSame($value, $this->data[0][$field]);
          }
      }
      """
    And a file named "features/table.feature" with:
      """
      Feature:
        Scenario:
          Given data:
            | username | age |
            | ever.zet | 22  |
          Then the "username" should be "ever.zet"
          And the "age" should be 22
      """
    When I run "behat -f progress --no-colors"
    Then it should pass with:
      """
      ...

      1 scenario (1 passed)
      3 steps (3 passed)
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
            | value          | type     |
            | "soeuhtou"     | string   |
            | 34             | integer  |
            | null           | NULL     |
            | 2 workdays ago | DateTime |
      """
    And a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      class FeatureContext implements Behat\Behat\Context\Context
      {
          private $value;

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
           * @Transform :number workdays ago
           */
          public function transformDate($number)
          {
              return new \DateTime("-$number days");
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
              if (gettype($this->value) != $type && get_class($this->value) != $type) {
                  throw new Exception("Expected " . $type . ", got " . gettype($this->value) . " (value: " . var_export($this->value, true) . ")");
              }
          }
      }
      """
    When I run "behat -f progress --no-colors"
    Then it should pass with:
      """
      ........

      4 scenarios (4 passed)
      8 steps (8 passed)
      """

  Scenario: By-type object transformations
    Given a file named "features/my.feature" with:
      """
      Feature:
        Scenario:
          Given I am "everzet"
          And he is "sroze"
          Then I should be a user named "everzet"
          And he should be a user named "sroze"
      """
    And a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php
      class User {
          public $name;
          private function __construct($name) { $this->name = $name; }
          static public function named($name) { return new static($name); }
      }
      class FeatureContext implements Behat\Behat\Context\Context
      {
          private $I;
          private $he;

          /** @Transform */
          public function userFromName($name) : User {
              return User::named($name);
          }

          /** @Given I am :user */
          public function iAm(User $user) {
              $this->I = $user;
          }

          /** @Given /^he is \"([^\"]+)\"$/ */
          public function heIs(User $user) {
              $this->he = $user;
          }

          /** @Then I should be a user named :name */
          public function iShouldHaveName($name) {
              if ('User' !== get_class($this->I)) {
                  throw new Exception("User expected, {gettype($this->I)} given");
              }
              if ($name !== $this->I->name) {
                  throw new Exception("Actual name is {$this->I->name}");
              }
          }

          /** @Then he should be a user named :name */
          public function heShouldHaveName($name) {
          if ('User' !== get_class($this->he)) {
                  throw new Exception("User expected, {gettype($this->he)} given");
              }
              if ($name !== $this->he->name) {
                  throw new Exception("Actual name is {$this->he->name}");
              }
          }
      }
      """
    When I run "behat -f progress --no-colors"
    Then it should pass with:
      """
      ....

      1 scenario (1 passed)
      4 steps (4 passed)
      """

  Scenario: By-type and by-name object transformations
    Given a file named "features/my.feature" with:
      """
      Feature:
        Scenario:
          Given I am "everzet"
          And she is "lunivore"
          Then I should be a user named "everzet"
          And she should be an admin named "admin: lunivore"
      """
    And a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php
      class User {
          public $name;
          private function __construct($name) { $this->name = $name; }
          static public function named($name) { return new static($name); }
      }
      class FeatureContext implements Behat\Behat\Context\Context
      {
          private $I;
          private $she;

          /** @Transform */
          public function userFromName($name) : User {
              return User::named($name);
          }

          /** @Transform :admin */
          public function adminFromName($name) : User {
              return User::named('admin: ' . $name);
          }

          /** @Transform :admin */
          public function adminString($name) {
              return 'admin';
          }

          /** @Given I am :user */
          public function iAm(User $user) {
              $this->I = $user;
          }

          /** @Given she is :admin */
          public function sheIs(User $admin) {
              $this->she = $admin;
          }

          /** @Then I should be a user named :name */
          public function iShouldHaveName($name) {
              if ('User' !== get_class($this->I)) {
                  throw new Exception("User expected, {gettype($this->I)} given");
              }
              if ($name !== $this->I->name) {
                  throw new Exception("Actual name is {$this->I->name}");
              }
          }

          /** @Then she should be an admin named :name */
          public function sheShouldHaveName($name) {
              if ('User' !== get_class($this->she)) {
                  throw new Exception("User expected, {gettype($this->she)} given");
              }
              if ($name !== $this->she->name) {
                  throw new Exception("Actual name is {$this->she->name}");
              }
          }
      }
      """
    When I run "behat -f progress --no-colors"
    Then it should pass with:
      """
      ....

      1 scenario (1 passed)
      4 steps (4 passed)
      """

  Scenario: Unicode Named Arguments Transformations
    Given a file named "features/step_arguments_unicode.feature" with:
      """
      Feature: Step Arguments
        Scenario:
          Given I am боб
          Then Username must be "боб"

        Scenario:
          Given I am "элис"
          Then Username must be "элис"
      """
    When I run "behat -f progress --no-colors"
    Then it should pass with:
      """
      ....

      2 scenarios (2 passed)
      4 steps (4 passed)
      """

  Scenario: Ordinal Arguments without quotes Transformations
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;
      use Behat\Gherkin\Node\TableNode;

      class FeatureContext implements Context
      {
          private $index;

          /** @Transform /^(0|[1-9]\d*)(?:st|nd|rd|th)?$/ */
          public function castToInt($number) {
            return intval($number) < PHP_INT_MAX ? intval($number) : $number;
          }

          /** @Given I pick the :index thing */
          public function iPickThing($index) {
              $this->index = $index;
          }

          /** @Then the index should be :value */
          public function theIndexShouldBe($value) {
              PHPUnit\Framework\Assert::assertSame($value, $this->index);
          }
      }
      """
    And a file named "features/ordinal_arguments.feature" with:
      """
      Feature: Ordinal Step Arguments
        Scenario:
          Given I pick the 1st thing
          Then the index should be "1"
        Scenario:
          Given I pick the "1st" thing
          Then the index should be "1"
        Scenario:
          Given I pick the 27th thing
          Then the index should be "27"
        Scenario:
          Given I pick the 5 thing
          Then the index should be "5"
      """
    When I run "behat -f progress --no-colors"
    Then it should pass with:
      """
      ........

      4 scenarios (4 passed)
      8 steps (8 passed)
      """

  @php8
  Scenario: By-type transformations don't trigger from union types
    Given a file named "features/union-transforms.feature" with:
      """
      Feature:
        Scenario:
          Given I am "everzet"
          And she is "lunivore"
      """
    And a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php
      class User {
      }
      class FeatureContext implements Behat\Behat\Context\Context
      {
          private $I;

          /** @Transform */
          public function userFromName($name) : User|int
          {
              return new User();
          }

          /**
           * @Given I am :user
           * @Given she is :user
           */
          public function iAm(User $user) {
              $this->I = $user;
          }
      }
      """
    When I run "behat -f progress --no-colors"
    Then it should fail with:
      """
      string given
      """

  Scenario: Return type transformations don't cause issues with scalar type hints (regression)
    Given a file named "features/scalar-transforms.feature" with:
      """
      Feature:

        Scenario:
          Then "string" should be passed
      """
    And a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      class FeatureContext implements Behat\Behat\Context\Context
      {
          /**
           * @Transform
           */
           public function transformToFoo($input): Foo
           {
           }

          /**
           * @Then :string should be passed
           */
          public function doSomething(string $job)
          {

          }
      }
      """
    When I run "behat -f progress --no-colors"
    Then it should pass
