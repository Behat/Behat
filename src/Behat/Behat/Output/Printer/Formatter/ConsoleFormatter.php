<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Printer\Formatter;

use Symfony\Component\Console\Formatter\OutputFormatter as BaseOutputFormatter;

/**
 * Symfony2 Console output formatter extended with custom highlighting tokens support.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ConsoleFormatter extends BaseOutputFormatter
{
    const CUSTOM_PATTERN = '/{\+([a-z-_]+)}(.*?){\-\\1}/si';

    /**
     * Formats a message according to the given styles.
     *
     * @param string $message The message to style
     *
     * @return string The styled message
     */
    public function format($message)
    {
        return preg_replace_callback(self::CUSTOM_PATTERN, array($this, 'replaceStyle'), $message);
    }

    /**
     * Replaces style of the output.
     *
     * @param array $match
     *
     * @return string The replaced style
     */
    private function replaceStyle($match)
    {
        if (!$this->isDecorated()) {
            return $match[2];
        }

        if ($this->hasStyle($match[1])) {
            $style = $this->getStyle($match[1]);
        } else {
            return $match[0];
        }

        return $style->apply($match[2]);
    }
}
