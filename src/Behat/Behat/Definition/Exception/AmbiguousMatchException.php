<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition\Exception;

use Behat\Behat\Definition\Definition;
use RuntimeException;

/**
 * Represents an exception caused by an ambiguous step definition match.
 *
 * If multiple definitions match the same step, behat is not able to determine which one is better and thus this
 * exception is thrown and test suite is stopped.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class AmbiguousMatchException extends RuntimeException implements SearchException
{
    /**
     * @var string
     */
    private $text;
    /**
     * @var Definition[]
     */
    private $matches = array();

    /**
     * Initializes ambiguous exception.
     *
     * @param string       $text    step description
     * @param Definition[] $matches ambiguous matches (array of Definition's)
     */
    public function __construct($text, array $matches)
    {
        $this->text = $text;
        $this->matches = $matches;

        $message = sprintf("Ambiguous match of \"%s\":", $text);
        foreach ($matches as $definition) {
            $message .= sprintf(
                "\nto `%s` from %s",
                $definition->getPattern(),
                $definition->getPath()
            );
        }

        parent::__construct($message);
    }
}
