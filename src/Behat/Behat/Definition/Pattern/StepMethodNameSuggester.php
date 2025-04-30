<?php

namespace Behat\Behat\Definition\Pattern;

/**
 * Suggests a suitable method name for a step definition based on the text of the step.
 *
 * Suggested names do not have to be unique - the ContextSnippetGenerator will add a numerical suffix if the Context
 * already contains a method with the suggested name.
 */
interface StepMethodNameSuggester
{
    public function suggest(string $stepTextWithoutPlaceholders): string;
}
