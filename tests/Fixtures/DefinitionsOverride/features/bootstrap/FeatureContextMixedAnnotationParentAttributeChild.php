<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Step\Then;

class FeatureContextMixedAnnotationParentAttributeChild extends ParentContextAnnotations implements Context
{
    #[Then(':token should be equal to :value')]
    public function shouldBe($token, $value): void
    {
    }
}
