<?php

namespace Everzet\Behat\StepDefinition;

use Behat\Gherkin\Node\TableNode;

/*
 * This file is part of the Behat.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Step Argument Transformation.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Transformation
{
    protected $regex;
    protected $callback;

    /**
     * Initialize new definition.
     *
     * @param   string      $regex      matching regular expression
     * @param   callback    $callback   callback
     */
    public function __construct($regex, $callback)
    {
        $this->regex        = $regex;
        $this->callback     = $callback;
    }

    /**
     * Return step matching regular expression.
     *
     * @return  string
     */
    public function getRegex()
    {
        return $this->regex;
    }

    /**
     * Transform passed argument or return false if can't. 
     * 
     * @param   mixed       $argument   step argument to transform
     *
     * @return  mixed|bool              transformed argument if regex matches or false
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
                    $this->callback
                  , $transformArguments[1 === count($transformArguments) ? 0 : 1]
                );
            }
        }

        return false;
    }
}
