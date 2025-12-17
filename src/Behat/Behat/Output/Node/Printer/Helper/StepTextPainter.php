<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer\Helper;

use Behat\Behat\Definition\Definition;
use Behat\Behat\Definition\Pattern\PatternTransformer;
use Behat\Behat\Util\StrictRegex;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Paints step text (with tokens) according to found definition.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class StepTextPainter
{
    /**
     * Initializes painter.
     */
    public function __construct(
        private readonly PatternTransformer $patternTransformer,
        private readonly ResultToStringConverter $resultConverter,
    ) {
    }

    /**
     * Colorizes step text arguments according to definition.
     *
     * @param string     $text
     *
     * @return string
     */
    public function paintText($text, Definition $definition, TestResult $result)
    {
        $regex = $this->patternTransformer->transformPatternToRegex($definition->getPattern());
        $style = $this->resultConverter->convertResultToString($result);
        $paramStyle = $style . '_param';

        // If it's just a string - skip
        if (!str_starts_with($regex, '/')) {
            return $text;
        }

        // Find arguments with offsets
        $matches = [];
        preg_match($regex, $text, $matches, PREG_OFFSET_CAPTURE);
        array_shift($matches);

        // Replace arguments with colorized ones
        $shift = 0;
        $lastReplacementPosition = 0;
        foreach ($matches as $key => $match) {
            if (!is_numeric($key) || -1 === $match[1] || str_contains($match[0], '<')) {
                continue;
            }

            $offset = $match[1] + $shift;
            $value = $match[0];

            // Skip inner matches
            if ($lastReplacementPosition > $offset) {
                continue;
            }
            $lastReplacementPosition = $offset + strlen($value);

            $begin = substr($text, 0, $offset);
            $end = substr($text, $lastReplacementPosition);
            $format = "{-$style}{+$paramStyle}%s{-$paramStyle}{+$style}";
            $text = sprintf("%s{$format}%s", $begin, $value, $end);

            // Keep track of how many extra characters are added
            $shift += strlen($format) - 2;
            $lastReplacementPosition += strlen($format) - 2;
        }

        // Replace "<", ">" with colorized ones
        $text = StrictRegex::replace(
            '/(<[^>]+>)/',
            "{-$style}{+$paramStyle}\$1{-$paramStyle}{+$style}",
            $text
        );

        return $text;
    }
}
