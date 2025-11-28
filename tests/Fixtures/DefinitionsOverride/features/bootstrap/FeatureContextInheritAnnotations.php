<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;

class FeatureContextInheritAnnotations extends ParentContextAnnotations implements Context
{
    public function shouldBe($token, $value): void
    {
    }
}
