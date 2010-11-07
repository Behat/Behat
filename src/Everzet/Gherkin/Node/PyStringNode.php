<?php

namespace Everzet\Gherkin\Node;

/*
 * This file is part of the Gherkin.
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
class PyStringNode
{
    protected $ltrimCount;
    protected $lines = array();

    public function __construct($string = null, $ltrimCount = 4)
    {
        $this->ltrimCount = $ltrimCount;

        if (null !== $string) {
            $string = preg_replace("/\r\n|\r/", "\n", $string);

            foreach (explode("\n", $string) as $line) {
                $this->addLine($line);
            }
        }
    }

    public function replaceTokens(array $tokens)
    {
        foreach ($tokens as $key => $value) {
            foreach (array_keys($this->lines) as $line) {
                $this->lines[$line] = str_replace('<'.$key.'>', $value, $this->lines[$line], $count);
            }
        }
    }

    public function addLine($line)
    {
        $this->lines[] = preg_replace('/^\s{1,'.$this->ltrimCount.'}/', '', $line);
    }

    public function getLines()
    {
        return $this->lines;
    }

    public function __toString()
    {
        return implode("\n", $this->lines);
    }
}
