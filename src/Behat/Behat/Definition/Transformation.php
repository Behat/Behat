<?php

namespace Behat\Behat\Definition;

use Behat\Gherkin\Node\TableNode;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Step arguments transformation.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Transformation
{
    /**
     * Transformation regex.
     *
     * @var     string
     */
    protected $regex;
    /**
     * Transformation callback.
     *
     * @var     Callback
     */
    protected $callback;

    /**
     * Initializes transformation.
     *
     * @param   string      $regex      transformation regex
     * @param   Callback    $callback   transformation callback
     */
    public function __construct($regex, $callback)
    {
        $this->regex        = $regex;
        $this->callback     = $callback;
    }

    /**
     * Returns transformation regex.
     *
     * @return  string
     */
    public function getRegex()
    {
        return $this->regex;
    }

    /**
     * Returns transformed argument or false if can't.
     *
     * @param   mixed       $argument   step argument to transform
     *
     * @return  mixed|bool              transformed argument or false
     */
    public function transform($argument)
    {
        if ($argument instanceof TableNode) {
            $tableMatching = 'table:' . implode(',', $argument->getRow(0));

            if (preg_match($this->regex, $tableMatching)) {
                return call_user_func($this->callback, $argument);
            }
        } elseif (is_string($argument) || $argument instanceof PyStringNode) {
            if (preg_match($this->regex, (string) $argument, $transformArguments)) {
                return call_user_func(
                    $this->callback,
                    $transformArguments[1 === count($transformArguments) ? 0 : 1]
                );
            }
        }

        return false;
    }
}
