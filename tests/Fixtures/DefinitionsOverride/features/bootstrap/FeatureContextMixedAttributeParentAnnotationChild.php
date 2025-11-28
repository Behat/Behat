<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;

class FeatureContextMixedAttributeParentAnnotationChild extends ParentContextAttributes implements Context
{
    /**
     * @Then :token should be equal to :value
     */
    public function shouldBe($token, $value): void
    {
    }
}
