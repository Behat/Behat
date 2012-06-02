<?php

namespace Behat\Behat\Exception;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Ambiguous exception.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class AmbiguousException extends BehaviorException
{
    protected $text;
    protected $matches = array();

    /**
     * Initializes ambiguous exception.
     *
     * @param string $text    step description
     * @param array  $matches ambigious matches (array of Definition's)
     */
    public function __construct($text, array $matches)
    {
        $this->text     = $text;
        $this->matches  = $matches;

        $message = sprintf("Ambiguous match of \"%s\":", $text);
        foreach ($matches as $definition) {
            $message .= sprintf("\nto `%s` from %s", $definition->getRegex(), $definition->getPath());
        }

        parent::__construct($message);
    }
}
