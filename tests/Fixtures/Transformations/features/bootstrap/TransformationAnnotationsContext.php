<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;

class TransformationAnnotationsContext implements Context
{
    /** @Transform /"([^\ "]+)(?: - (\d+))?" user/ */
    public function createUserFromUsername(string $username, int $age = 20): User
    {
        return new User($username, $age);
    }

    /** @Transform table:username,age */
    public function createUserFromTable(TableNode $table): User
    {
        $hash     = $table->getHash();
        $username = $hash[0]['username'];
        $age      = $hash[0]['age'];

        return new User($username, $age);
    }

    /** @Transform table:%username@,age# */
    public function createUserFromTableWithSymbol(TableNode $table): User
    {
        $hash     = $table->getHash();
        $username = $hash[0]['%username@'];
        $age      = $hash[0]['age#'];

        return new User($username, $age);
    }

    /** @Transform table:логин,возраст */
    public function createUserFromTableInRussian(TableNode $table)
    {
        $hash     = $table->getHash();
        $username = $hash[0]['логин'];
        $age      = $hash[0]['возраст'];

        return new User($username, $age);
    }

    /** @Transform rowtable:username,age */
    public function createUserFromRowTable(TableNode $table): User
    {
        $hash     = $table->getRowsHash();
        $username = $hash['username'];
        $age      = $hash['age'];

        return new User($username, $age);
    }

    /** @Transform rowtable:--username,age */
    public function createUserFromRowTableWithSymbol(TableNode $table): User
    {
        $hash     = $table->getRowsHash();
        $username = $hash['--username'];
        $age      = $hash['age'];

        return new User($username, $age);
    }

    /** @Transform rowtable:логин,возраст */
    public function createUserFromRowTableInRussian(TableNode $table)
    {
        $hash     = $table->getRowsHash();
        $username = $hash['логин'];
        $age      = $hash['возраст'];

        return new User($username, $age);
    }

    /** @Transform row:username */
    public function createUserNamesFromTable(array $tableRow): string
    {
        return $tableRow['username'];
    }

    /** @Transform row:$username */
    public function createUserNamesFromTableWithSymbol(array $tableRow): string
    {
        return $tableRow['$username'];
    }

    /** @Transform row:логин */
    public function createUserNamesFromTableInRussian($tableRow)
    {
        return $tableRow['логин'];
    }

    /** @Transform column:user,other user */
    public function convertUsernameToUserInColumn(string $name): User
    {
        return new User($name);
    }

    /** @Transform column:логин */
    public function convertUsernameInRussianToUserInColumn(string $name): User
    {
        return new User($name);
    }

    /** @Transform column:username */
    public function convertUsernameToUserWithUsernameHeading(string $name): User
    {
        throw new Exception('This should not be called');
    }

    /** @Transform column:hex age */
    public function convertHexAgeToAge(string $hexAge): int
    {
        return hexdec($hexAge);
    }

    /** @Transform /^\d+$/ */
    public function castToNumber(string $number): int
    {
        return intval($number);
    }

    /** @Transform :user */
    public function castToUser(string $username): User
    {
        return new User($username);
    }

    /**
     * @Transform /^(yes|no)$/
     */
    public function castYesOrNoToBoolean(string $expected): bool
    {
        return 'yes' === $expected;
    }
}
