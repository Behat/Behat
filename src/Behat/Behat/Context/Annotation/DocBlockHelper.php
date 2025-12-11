<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Annotation;

/**
 * Helper class for DocBlock parsing.
 */
class DocBlockHelper
{
    /**
     * Extracts a description from the provided docblock,
     * with support for multiline descriptions.
     */
    public function extractDescription(string $docBlock): string
    {
        // Remove indentation
        $description = preg_replace('/^[\s\t]*/m', '', $docBlock);

        // normalize line endings
        $description = str_replace("\r\n", "\n", (string) $description);

        // Remove block comment syntax
        $description = preg_replace('/^\/\*\*\s*|^\s*\*\s|^\s*\*\/$/m', '', (string) $description);

        // Remove annotations
        $description = preg_replace('/^@.*$/m', '', (string) $description);

        // Ignore docs after a "--" separator
        if (preg_match('/^--.*$/m', (string) $description)) {
            $descriptionParts = preg_split('/^--.*$/m', (string) $description);
            $description = array_shift($descriptionParts);
        }

        // Trim leading and trailing newlines
        return trim((string) $description, "\r\n");
    }
}
