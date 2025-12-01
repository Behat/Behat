<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Step\Given;

final class FirstContextTypehinted implements Context
{
    public function __construct(ParentClass $parent, ChildClass $child)
    {
    }

    #[Given('foo')]
    public function foo(): void
    {
    }
}
