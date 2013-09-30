<?php

namespace Behat\Behat\Snippet\Util;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Turnip parser.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class Turnip
{
    const PLACEHOLDER_REGEXP = "/\\\:(\w+)/";
    const OPTIONAL_WORD_REGEXP = '/(\s)?\\\\\(([^\\\]+)\\\\\)(\s)?/';
    const ALTERNATIVE_WORD_REGEXP = '/(\w+)\\\\\/(\w+)/';

    /**
     * Transforms turnip text to regular expression.
     *
     * @param string $turnip
     *
     * @return string
     */
    public static function turnipToRegex($turnip)
    {
        $regex = preg_quote($turnip, '/');

        // placeholder
        $regex = preg_replace_callback(self::PLACEHOLDER_REGEXP, function ($match) {
            return sprintf("[\"']?(?P<%s>(?<=\")[^\"]+(?=\")|(?<=')[^']+(?=')|(?<=\s)\w+(?=\s|$))['\"]?", $match[1]);
        }, $regex);

        // optional word
        $regex = preg_replace(self::OPTIONAL_WORD_REGEXP, '(?:\1)?(?:\2)?(?:\3)?', $regex);

        // alternative word
        $regex = preg_replace(self::ALTERNATIVE_WORD_REGEXP, '(?:\1|\2)', $regex);

        return '/^' . $regex . '$/';
    }
}
