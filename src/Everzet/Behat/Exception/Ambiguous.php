<?php

namespace Everzet\Behat\Exception;

use Everzet\Behat\Output\Formatter\ConsoleFormatter as Formatter;

/*
 * This file is part of the Behat.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Ambiguous Exception.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Ambiguous extends BehaviorException
{
    protected $text;
    protected $matches = array();

    /**
     * Initialize exception.
     *
     * @param   string  $text       step description
     * @param   array   $matches    ambigious matches (array of StepDefinition's)
     */
    public function __construct($text, array $matches)
    {
        parent::__construct();

        $this->text     = $text;
        $this->matches  = $matches;

        $this->message = sprintf("Ambiguous match of \"%s\":", $this->text);
        foreach ($this->matches as $definition){
            $this->message .= sprintf("\n%s:%d:in `%s`",
                Formatter::trimFilename($definition->getFile()), $definition->getLine(), $definition->getRegex()
            );
        }
    }
}
