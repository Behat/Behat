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
 * Table.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class TableNode
{
    protected $colSplitter;
    protected $rows = array();

    public function __construct($colSplitter = '|')
    {
        $this->colSplitter = $colSplitter;
    }

    public function addRow($row)
    {
        $this->rows[] = array_map(function($item) {
            return trim($item);
        }, explode($this->colSplitter, $row));
    }

    public function getRows()
    {
        return $this->rows;
    }

    public function getRow($rowNum)
    {
        return $this->rows[$rowNum];
    }

    public function getRowAsString($rowNum)
    {
        $values = array();
        foreach ($this->getRow($rowNum) as $col => $value) {
            $values[] = $this->padRight(' '.$value.' ', $this->getMaxLengthForColumn($col) + 2);
        }

        return sprintf($this->colSplitter . '%s' . $this->colSplitter, implode($this->colSplitter, $values));
    }

    public function getHash()
    {
        $rows = $this->getRows();
        $keys = array_shift($rows);

        $hash = array();
        foreach ($rows as $row) {
            $hash[] = array_combine($keys, $row);
        }

        return $hash;
    }

    public function __toString()
    {
        $string = '';

        for ($i = 0; $i < count($this->getRows()); $i++) {
            if ('' !== $string) {
                $string .= "\n";
            }
            $string .= $this->getRowAsString($i);
        }

        return $string;
    }

    protected function getMaxLengthForColumn($columnNum)
    {
        $max = 0;

        foreach ($this->getRows() as $row) {
            if (($tmp = mb_strlen($row[$columnNum])) > $max) {
                $max = $tmp;
            }
        }

        return $max;
    }

    protected function padRight($text, $length)
    {
        while ($length > mb_strlen($text)) {
            $text = $text . ' ';
        }

        return $text;
    }
}
