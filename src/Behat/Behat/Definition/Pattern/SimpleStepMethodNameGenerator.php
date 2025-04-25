<?php

namespace Behat\Behat\Definition\Pattern;

use Behat\Transliterator\Transliterator;

class SimpleStepMethodNameGenerator implements StepMethodNameGenerator
{
    public function generate(string $stepTextWithoutPlaceholders): string
    {
        $canonicalText = Transliterator::transliterate($stepTextWithoutPlaceholders, ' ');
        $canonicalText = preg_replace('/[^a-zA-Z\_\ ]/', '', $canonicalText);

        return str_replace(' ', '', ucwords($canonicalText));
    }
}
