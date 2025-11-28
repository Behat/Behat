<?php

declare(strict_types=1);

use Behat\Step\Then;

class ParentContextAttributes
{
    #[Then(':token should be :value')]
    public function shouldBe($token, $value): void
    {
    }
}
