<?php

namespace Behat\Behat\Output\Formatter;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Verbosable Formatter Interface.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface VerbosableFormatterInterface
{
    /**
     * Set formatter to be verbose. 
     * 
     * @param   boolean $verbose    verbose
     */
    public function beVerbose($verbose = true);
}

