<?php

namespace Everzet\Behat\Exception;

/*
 * This file is part of the behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Ambiguous Exception.
 *
 * @package     Behat
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Ambiguous extends BehaviorException
{
    protected $text;
    protected $matches = array();

    /**
     * Creates exception
     *
     * @param   string  $text       step description
     * @param   array   $matches    ambigious matches (array of StepDefinition's)
     */
    public function __construct($text, array $matches)
    {
        parent::__construct();

        $this->definition = $text;
        $this->matches    = $matches;

        $this->message = sprintf("Ambiguous match of \"%s\":", $this->text);
        foreach ($this->matches as $definition){
            $this->message .= sprintf("\n%s:%d:in `%s`",
                $definition->getFile(), $definition->getLine(), $definition->getRegex()
            );
        }
    }
}
