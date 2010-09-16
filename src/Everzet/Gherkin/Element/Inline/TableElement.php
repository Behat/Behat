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
 * Table.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class TableElement
{
    protected $rowSplitter;
    protected $keys = array();
    protected $values = array();

    public function __construct($rowSplitter = '|')
    {
        $this->rowSplitter = $rowSplitter;
    }

    public function addRow($row)
    {
        $items = array_map(function($item) {
            return trim($item);
        }, explode($this->rowSplitter, $row));

        if (empty($this->keys)) {
            $this->keys = $items;
        } else {
            $this->values[] = $items;
        }
    }

    protected function padRight($text, $length)
    {
        while ($length > mb_strlen($text)) {
            $text = $text . ' ';
        }

        return $text;
    }

    public function getKeysAsString()
    {
        $keys = array();
        foreach ($this->keys as $col => $key) {
            $keys[] = $this->padRight(' '.$key.' ', $this->getMaxLengthForColumn($col) + 2);
        }

        return sprintf('|%s|', implode('|', $keys));
    }

    public function getRowAsString($rowNum)
    {
        $values = array();
        foreach ($this->values[$rowNum] as $col => $value) {
            $values[] = $this->padRight(' '.$value.' ', $this->getMaxLengthForColumn($col) + 2);
        }

        return sprintf('|%s|', implode('|', $values));
    }

    public function __toString()
    {
        $string = $this->getKeysAsString();
        for ($i = 0; $i < count($this->values); $i++) {
            $string .= "\n" . $this->getRowAsString($i);
        }

        return $string;
    }

    public function getMaxLengthForColumn($columnNum)
    {
        $key = $this->keys[$columnNum];
        $max = mb_strlen($key);

        foreach ($this->getHash() as $row) {
            if (($tmp = mb_strlen($row[$key])) > $max) {
                $max = $tmp;
            }
        }

        return $max;
    }

    public function getKeys()
    {
        return $this->keys;
    }

    public function getValues()
    {
        return $this->values;
    }

    public function getHash()
    {
        $hash = array();

        foreach ($this->values as $rowValues) {
            $hash[] = array_combine($this->keys, $rowValues);
        }

        return $hash;
    }
}
