<?php

namespace Everzet\Behat\Exceptions;

/*
 * This file is part of the behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Ambiguous extends BehaviorException
{
    protected $text;
    protected $matches = array();

    public function __construct($text, array $matches)
    {
        $this->definition = $definition;
        $this->matches = $matches;

        parent::__construct();
    }

    public function __toString()
    {
        $string = sprintf("Ambiguous match of \"%s\":", $this->text);

        foreach ($this->matches as $definition){
            $string .= sprintf("\n%s:%d:in `%s`",
                $definition->getFile(), $definition->getLine(), $definition->getRegex()
            );
        }

        return $string;
    }
}