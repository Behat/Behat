<?php

namespace Behat\Behat\Definition\Pattern;

/**
 * Takes the text of a step and converts it to a suitable form for use in a method name.
 */
interface StepMethodNameGenerator
{
    public function generate(string $stepTextWithoutPlaceholders): string;
}
