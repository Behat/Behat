<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer\Pretty;

use Behat\Behat\Definition\Definition;
use Behat\Behat\Definition\Pattern\PatternTransformer;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Behat step text painter.
 *
 * Paints step text (with tokens) according to found definition.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class StepTextPainter
{
    /**
     * @var PatternTransformer
     */
    private $patternTransformer;

    /**
     * Initializes painter.
     *
     * @param PatternTransformer $patternTransformer
     */
    public function __construct(PatternTransformer $patternTransformer)
    {
        $this->patternTransformer = $patternTransformer;
    }

    /**
     * Colorizes step text arguments according to definition.
     *
     * @param string     $text
     * @param Definition $definition
     * @param TestResult $result
     *
     * @return string
     */
    public function paintText($text, Definition $definition, TestResult $result)
    {
        $regex = $this->patternTransformer->transformPatternToRegex($definition->getPattern());
        $paramStyle = $result . '_param';

        // If it's just a string - skip
        if ('/' !== substr($regex, 0, 1)) {
            return $text;
        }

        // Find arguments with offsets
        $matches = array();
        preg_match($regex, $text, $matches, PREG_OFFSET_CAPTURE);
        array_shift($matches);

        // Replace arguments with colorized ones
        $shift = 0;
        $lastReplacementPosition = 0;
        foreach ($matches as $key => $match) {
            if (!is_numeric($key) || -1 === $match[1] || false !== strpos($match[0], '<')) {
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
            $format = "{-$result}{+$paramStyle}%s{-$paramStyle}{+$result}";
            $text = sprintf("%s{$format}%s", $begin, $value, $end);

            // Keep track of how many extra characters are added
            $shift += strlen($format) - 2;
            $lastReplacementPosition += strlen($format) - 2;
        }

        // Replace "<", ">" with colorized ones
        $text = preg_replace(
            '/(<[^>]+>)/',
            "{-$result}{+$paramStyle}\$1{-$paramStyle}{+$result}",
            $text
        );

        return $text;
    }
}
