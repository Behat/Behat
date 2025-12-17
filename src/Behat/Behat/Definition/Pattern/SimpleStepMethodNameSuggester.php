<?php

namespace Behat\Behat\Definition\Pattern;

use Behat\Behat\Util\StrictRegex;

final class SimpleStepMethodNameSuggester implements StepMethodNameSuggester
{
    public const DEFAULT_NAME = 'stepDefinition1';

    public function suggest(string $stepTextWithoutPlaceholders): string
    {
        // Put the text in title case first so that it will become CamelCase when we strip strings
        $name = mb_convert_case($stepTextWithoutPlaceholders, MB_CASE_TITLE);

        // Remove characters that are never valid in a method name
        $name = StrictRegex::replace('/[^a-zA-Z0-9_\x80-\xff]/u', '', $name);

        // Remove leading digits (these are the only characters that are valid in a name except at the beginning)
        $name = StrictRegex::replace('/^\d+/', '', $name);

        if ($name === '') {
            // ContextSnippetGenerator::getUniqueMethodName will increment the trailing number if necessary so that
            // all step methods in a Context have unique names (e.g. stepDefinition2, stepDefinition3, etc).
            return self::DEFAULT_NAME;
        }

        // Switch from CamelCase to camelCase
        return mb_lcfirst($name);
    }
}
