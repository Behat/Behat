<?php

declare(strict_types=1);

use Behat\Behat\Context\CustomSnippetAcceptingContext;

final class FeatureContextRegex implements CustomSnippetAcceptingContext
{
    public static function getAcceptedSnippetType(): string
    {
        return 'regex';
    }
}
