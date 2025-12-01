<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Step\Given;

final class ExceptionHandlerContext implements Context
{
    #[Given('non-existent class')]
    public function nonexistentClass(): void
    {
        $ins = new Non\Existent\Cls();
    }

    #[Given('non-existent method')]
    public function nonexistentMethod(): void
    {
        $this->getName();
    }
}
