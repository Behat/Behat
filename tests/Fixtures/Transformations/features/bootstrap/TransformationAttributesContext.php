<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Transformation\Transform;

class TransformationAttributesContext implements Context
{
    #[Transform('/"([^\ "]+)(?: - (\d+))?" user/')]
    public function createUserFromUsername(string $username, int $age = 20): User
    {
        return new User($username, $age);
    }

    #[Transform('table:username,age')]
    public function createUserFromTable(TableNode $table): User
    {
        $hash = $table->getHash();
        $username = $hash[0]['username'];
        $age = $hash[0]['age'];

        return new User($username, $age);
    }

    #[Transform('table:%username@,age#')]
    public function createUserFromTableWithSymbol(TableNode $table): User
    {
        $hash = $table->getHash();
        $username = $hash[0]['%username@'];
        $age = $hash[0]['age#'];

        return new User($username, $age);
    }

    #[Transform('table:логин,возраст')]
    public function createUserFromTableInRussian(TableNode $table)
    {
        $hash = $table->getHash();
        $username = $hash[0]['логин'];
        $age = $hash[0]['возраст'];

        return new User($username, $age);
    }

    #[Transform('rowtable:username,age')]
    public function createUserFromRowTable(TableNode $table): User
    {
        $hash = $table->getRowsHash();
        $username = $hash['username'];
        $age = $hash['age'];

        return new User($username, $age);
    }

    #[Transform('rowtable:--username,age')]
    public function createUserFromRowTableWithSymbol(TableNode $table): User
    {
        $hash = $table->getRowsHash();
        $username = $hash['--username'];
        $age = $hash['age'];

        return new User($username, $age);
    }

    #[Transform('rowtable:логин,возраст')]
    public function createUserFromRowTableInRussian(TableNode $table)
    {
        $hash = $table->getRowsHash();
        $username = $hash['логин'];
        $age = $hash['возраст'];

        return new User($username, $age);
    }

    #[Transform('row:username')]
    public function createUserNamesFromTable(array $tableRow): string
    {
        return $tableRow['username'];
    }

    #[Transform('row:$username')]
    public function createUserNamesFromTableWithSymbol(array $tableRow): string
    {
        return $tableRow['$username'];
    }

    #[Transform('row:логин')]
    public function createUserNamesFromTableInRussian($tableRow)
    {
        return $tableRow['логин'];
    }

    #[Transform('/^\d+$/')]
    public function castToNumber(string $number): int
    {
        return intval($number);
    }

    #[Transform(':user')]
    public function castToUser(string $username): User
    {
        return new User($username);
    }

    #[Transform('/^(yes|no)$/')]
    public function castYesOrNoToBoolean(string $expected): bool
    {
        return 'yes' === $expected;
    }
}
