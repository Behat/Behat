<?php

namespace Everzet\Gherkin\Element\Inline;

/*
 * This file is part of the behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * PyString.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class PyStringElement
{
    protected $ltrimCount;
    protected $lines = array();

    public function __construct($ltrimCount = 4)
    {
        $this->ltrimCount = $ltrimCount;
    }

    public function addLine($line)
    {
        $this->lines[] = preg_replace('/^\s{0,'.$this->ltrimCount.'}/', '', $line);
    }

    public function __toString()
    {
        return implode("\n", $this->lines);
    }
}
