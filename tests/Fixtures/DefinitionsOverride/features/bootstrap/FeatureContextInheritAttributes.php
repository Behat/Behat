<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;

class FeatureContextInheritAttributes extends ParentContextAttributes implements Context
{
    public function shouldBe($token, $value): void
    {
    }
}
