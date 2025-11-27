<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use PHPUnit\Framework\Assert;

class FirstContext implements Context
{
    private array $foo;

    public function __construct(array $foo)
    {
        $this->foo = $foo;
    }

    #[Given('foo')]
    public function foo(): void
    {
        Assert::assertIsArray($this->foo);
        Assert::assertSame('foo', $this->foo[0]);
        Assert::assertSame('bar', $this->foo[1]);
    }
}
