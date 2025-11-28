<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Step\Then;

class TokenStartAttributes implements Context
{
    #[Then(':token should be :value')]
    public function shouldBe($token, $value): void
    {
    }
}
