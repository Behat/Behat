<?php

use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;

class UserAnnotationsContext implements Context
{
    private User $user;

    /**
     * @Given /I am (".*" user)/
     * @Given I am user:
     * @Given I am :user
     */
    public function iAmUser(User $user): void {
        $this->user = $user;
    }

    /**
     * @Then /Username must be "([^"]+)"/
     */
    public function usernameMustBe(string $username): void {
        Assert::assertEquals($username, $this->user->getUsername());
    }

    /**
     * @Then /Age must be (\d+)/
     */
    public function ageMustBe(int $age): void {
        Assert::assertEquals($age, $this->user->getAge());
        Assert::assertIsInt($age);
    }

    /**
     * @Then the Usernames must be:
     */
    public function usernamesMustBe(array $usernames): void {
        Assert::assertEquals($usernames[0], $this->user->getUsername());
    }

    /**
     * @Then /^the boolean (no) should be transformed to false$/
     */
    public function theBooleanShouldBeTransformed(bool $boolean): void {
        Assert::assertSame(false, $boolean);
    }
}
